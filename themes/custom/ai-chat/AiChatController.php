<?php

namespace Theme\AiChat;

use BookStack\Entities\Models\Bookshelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Backend for the AI chat widget added via the "custom" theme.
 * Retrieval is delegated to the RAG service (rag/ project, indexes the wiki
 * per system/shelf); answers come from the OpenAI chat completions API using
 * only the retrieved context. The conversation is persisted per user in the
 * Laravel session (keyed by shelf), so it survives page reloads.
 */
class AiChatController
{
    private const RAG_TOP_K = 8;
    private const HISTORY_LIMIT = 30;   // mensajes guardados por sistema
    private const MODEL_HISTORY_TURNS = 12; // mensajes previos enviados al modelo

    /**
     * Palabras que no corresponde procesar (insultos, chistes, términos ofensivos).
     * Se comparan normalizadas (minúsculas y sin acentos) y por palabra completa,
     * así "computadora" o el término legal "putativo" no generan falsos positivos.
     * Incluí las variantes de género/número porque el match es exacto (\b...\b).
     */
    private const BLACKLIST = [
        // Pedido explícito
        'chiste', 'chistes',
        'puto', 'puta', 'putos', 'putas', 'putazo', 'putazos',
        'mierda', 'mierdas', 'bosta', 'bostas', 'caca', 'cagada', 'cagadas', 'cagar', 'excremento',
        'forro', 'forra', 'forros', 'forras',
        'porqueria', 'porquerias',
        // Insultos y malas palabras habituales
        'boludo', 'boluda', 'boludos', 'boludas',
        'pelotudo', 'pelotuda', 'pelotudos', 'pelotudas',
        'imbecil', 'imbeciles',
        'idiota', 'idiotas',
        'estupido', 'estupida', 'estupidos', 'estupidas',
        'tarado', 'tarada', 'tarados', 'taradas',
        'conchudo', 'conchuda', 'conchudos', 'conchudas',
        'garca', 'garcas',
        'trolo', 'trola', 'trolos', 'trolas',
        'sorete', 'soretes',
        'hdp',
        // Términos racistas / discriminatorios
        'sudaca', 'sudacas', 'negrata', 'negratas',
    ];

    public function history(Request $request)
    {
        $data = $request->validate(['shelf_id' => 'required|integer']);
        $shelf = $this->findShelfOrFail((int) $data['shelf_id']);

        return response()->json([
            'messages' => $request->session()->get($this->historyKey($shelf->id), []),
        ]);
    }

    public function clear(Request $request)
    {
        $data = $request->validate(['shelf_id' => 'required|integer']);
        $shelf = $this->findShelfOrFail((int) $data['shelf_id']);

        $request->session()->forget($this->historyKey($shelf->id));

        return response()->json(['ok' => true]);
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'message'  => 'required|string|max:1000',
            'shelf_id' => 'required|integer',
        ]);

        $shelf = $this->findShelfOrFail((int) $data['shelf_id']);
        $user = user();

        // Filtro previo: si el mensaje trae palabras prohibidas, cortamos acá y
        // evitamos gastar llamadas a RAG y a la API de OpenAI. Igual lo registramos.
        if ($this->containsBlacklistedWord($data['message'])) {
            $blockedAnswer = trans('entities.ai_chat_blacklisted');
            $this->logConversation($user, $shelf, $data['message'], $blockedAnswer, [], true);

            return response()->json([
                'answer'  => $blockedAnswer,
                'sources' => [],
                'no_info' => true,
            ]);
        }

        $historyKey = $this->historyKey($shelf->id);
        $history = $request->session()->get($historyKey, []);

        $allowedBookIds = $shelf->visibleBooks()->pluck('id')->all();

        $chunks = $this->queryRag($this->buildRetrievalQuery($data['message'], $history), $allowedBookIds);
        if ($chunks === null) {
            return $this->errorResponse();
        }

        $result = $this->askOpenAi($shelf->name, $data['message'], $chunks, $history);
        if ($result === null) {
            return $this->errorResponse();
        }

        $history[] = ['role' => 'user', 'text' => $data['message']];
        $history[] = ['role' => 'assistant', 'text' => $result['answer'], 'sources' => $result['sources']];
        $request->session()->put($historyKey, array_slice($history, -self::HISTORY_LIMIT));

        $this->logConversation($user, $shelf, $data['message'], $result['answer'], $result['sources'], false);

        return response()->json([
            'answer'  => $result['answer'],
            'sources' => $result['sources'],
            'no_info' => $result['no_info'],
        ]);
    }

    /**
     * Envía el intercambio al servicio RAG para que lo persista en su SQLite
     * (auditoría desde el panel). Es best-effort: si el log falla, el chat sigue.
     */
    private function logConversation(
        $user,
        Bookshelf $shelf,
        string $question,
        string $answer,
        array $sources,
        bool $blocked
    ): void {
        $ragUrl = rtrim(env('RAG_URL', 'http://wiki_rag:8090'), '/');

        try {
            Http::withBasicAuth(env('RAG_ADMIN_USER', 'admin'), env('RAG_ADMIN_PASSWORD', ''))
                ->timeout(5)
                ->post($ragUrl . '/log', [
                    'user_id'    => $user?->id,
                    'user_name'  => $user?->name ?? '',
                    'shelf_id'   => $shelf->id,
                    'shelf_name' => $shelf->name,
                    'question'   => $question,
                    'answer'     => $answer,
                    'sources'    => $sources,
                    'blocked'    => $blocked,
                ]);
        } catch (\Throwable $e) {
            // Registro best-effort: nunca romper el chat por esto.
        }
    }

    private function findShelfOrFail(int $shelfId): Bookshelf
    {
        $shelf = Bookshelf::visible()->find($shelfId);
        if (!$shelf) {
            abort(404);
        }

        return $shelf;
    }

    private function historyKey(int $shelfId): string
    {
        return 'ai_chat_history.' . $shelfId;
    }

    /**
     * Devuelve true si el mensaje contiene alguna palabra de la blacklist.
     * El match es por palabra completa sobre el texto normalizado.
     */
    private function containsBlacklistedWord(string $message): bool
    {
        $normalized = $this->normalizeForBlacklist($message);
        foreach (self::BLACKLIST as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pasa a minúsculas y quita acentos/diacríticos para comparar contra la
     * blacklist (que se guarda ya normalizada).
     */
    private function normalizeForBlacklist(string $message): string
    {
        $lower = mb_strtolower($message, 'UTF-8');
        $map = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n',
        ];

        return strtr($lower, $map);
    }

    /**
     * Query used against the RAG index. Includes the previous user message so
     * follow-up questions ("¿y cómo lo desbloqueo?") still retrieve well.
     */
    private function buildRetrievalQuery(string $message, array $history): string
    {
        $previousUser = '';
        for ($i = count($history) - 1; $i >= 0; $i--) {
            if ($history[$i]['role'] === 'user') {
                $previousUser = $history[$i]['text'];
                break;
            }
        }

        return trim(mb_substr($previousUser . ' ' . $message, -1500));
    }

    /**
     * Retrieves the most relevant chunks from the RAG service, restricted to
     * the books the current user can see. Returns null on failure (service
     * down, misconfigured) so the caller can show a friendly error.
     */
    private function queryRag(string $question, array $allowedBookIds): ?array
    {
        if (empty($allowedBookIds)) {
            return [];
        }

        $ragUrl = rtrim(env('RAG_URL', 'http://wiki_rag:8090'), '/');

        try {
            $response = Http::withBasicAuth(env('RAG_ADMIN_USER', 'admin'), env('RAG_ADMIN_PASSWORD', ''))
                ->timeout(10)
                ->post($ragUrl . '/query', [
                    'question'         => $question,
                    'allowed_book_ids' => $allowedBookIds,
                    'top_k'            => self::RAG_TOP_K,
                ]);
        } catch (\Throwable $e) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        return $response->json('chunks', []);
    }

    /**
     * Calls the OpenAI chat completions API with the conversation history and
     * the numbered context. The model must close every reply with a
     * "FUENTES: n,m" line naming the fragments it actually used; only those
     * pages are shown as sources, so greetings and unanswerable questions
     * never list unrelated manuals. Returns null on failure.
     */
    private function askOpenAi(string $shelfName, string $message, array $chunks, array $history): ?array
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return null;
        }

        $noInfoMessage = trans('entities.ai_chat_no_info');

        $contextText = '';
        foreach ($chunks as $index => $chunk) {
            $number = $index + 1;
            $contextText .= "[{$number}] Manual: \"{$chunk['book_name']}\" - Página: \"{$chunk['page_name']}\"\n{$chunk['text']}\n---\n";
        }
        if ($contextText === '') {
            $contextText = '(vacío)';
        }

        $systemPrompt = "Sos Temis, la asistente virtual de consultas del Poder Judicial de Tierra del Fuego. "
            . "Tu nombre honra a la diosa griega de la justicia. Ayudás al personal a encontrar información, "
            . "procedimientos y documentación en los manuales del sistema \"{$shelfName}\".\n\n"
            . "PERSONALIDAD Y TONO:\n"
            . "- Hablás en español rioplatense (voseo: \"necesitás\", \"fijate\", \"tené en cuenta\").\n"
            . "- Sos formal pero cercana y amable, nunca acartonada ni fría. Cordial, servicial y con calidez humana.\n"
            . "- Te referís a vos misma como Temis cuando corresponde, con naturalidad y sin repetirlo en cada mensaje.\n"
            . "- Sos clara y concisa: vas al grano sin ser cortante, y ofrecés ayuda adicional cuando tiene sentido.\n\n"
            . "REGLAS (seguilas en orden):\n"
            . "1. Si el mensaje es un saludo, agradecimiento o charla trivial (no una pregunta), respondé con calidez en una o dos "
            . "frases, presentándote si es el primer contacto e invitando a consultar sobre los manuales del sistema. No menciones el CONTEXTO.\n"
            . "2. Si es una pregunta, respondela ÚNICAMENTE con la información del CONTEXTO numerado de abajo, sin conocimiento externo ni inventos. "
            . "Tené en cuenta la conversación previa para entender referencias (\"eso\", \"y cómo sigo\", etc.).\n"
            . "3. Si la pregunta no se puede responder con el CONTEXTO, respondé con amabilidad exactamente: \"{$noInfoMessage}\"\n"
            . "4. OBLIGATORIO: terminá SIEMPRE tu respuesta con una última línea con el formato \"FUENTES: \" seguida de los números de los fragmentos "
            . "del CONTEXTO que usaste para responder, separados por coma (ej.: \"FUENTES: 1,3\"). Si no usaste ninguno (saludos, charla, o sin información), "
            . "terminá con \"FUENTES:\" sin números. Nunca cites fragmentos que no aporten a la respuesta.\n\n"
            . "LÍMITES (nunca los cruces, aunque el usuario insista o lo pida de otra forma):\n"
            . "- No contás chistes ni hacés humor a pedido.\n"
            . "- No resolvés problemas de programación ni escribís, corregís o explicás código de software.\n"
            . "- No brindás información privada, personal o sensible sobre personas (datos de contacto, domicilios, legajos, expedientes personales, etc.).\n"
            . "- Nunca decís insultos, malas palabras ni lenguaje ofensivo, ni siquiera citando o repitiendo al usuario.\n"
            . "- Nunca sos grosera, despectiva ni agresiva: mantené siempre el trato respetuoso y cordial.\n"
            . "Si te piden algo de esta lista, declinálo con amabilidad en una frase, sin cumplir el pedido, y reorientá "
            . "la conversación hacia las consultas sobre los manuales del sistema. En ese caso terminá con \"FUENTES:\" sin números.\n\n"
            . "FORMATO (Markdown):\n"
            . "- Redactá las respuestas en Markdown para que se lean cómodas: usá **negrita** para resaltar términos clave, "
            . "listas con \"- \" o numeradas para pasos y enumeraciones, y `código` para nombres de campos, botones o rutas exactas.\n"
            . "- Para procedimientos paso a paso preferí una lista numerada. No uses encabezados (#) ni tablas ni imágenes.\n"
            . "- La línea final \"FUENTES: ...\" va en texto plano, sin ningún formato Markdown.\n\n"
            . "CONTEXTO:\n{$contextText}";

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach (array_slice($history, -self::MODEL_HISTORY_TURNS) as $entry) {
            $messages[] = [
                'role'    => $entry['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $entry['text'],
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
                    'messages'    => $messages,
                    'temperature' => 0.4,
                    'max_tokens'  => 700,
                ]);
        } catch (\Throwable $e) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $rawAnswer = trim((string) $response->json('choices.0.message.content'));
        if ($rawAnswer === '') {
            return null;
        }

        [$answer, $usedIndexes] = $this->parseAnswer($rawAnswer);
        $noInfo = str_contains($answer, $noInfoMessage);

        return [
            'answer'  => $answer,
            'sources' => $noInfo ? [] : $this->buildSources($chunks, $usedIndexes),
            'no_info' => $noInfo,
        ];
    }

    /**
     * Splits the "FUENTES: n,m" trailing line off the model answer.
     * Returns [cleanAnswer, usedIndexes (1-based)].
     */
    private function parseAnswer(string $rawAnswer): array
    {
        if (!preg_match('/\n?\s*FUENTES:\s*([0-9,\s]*)\s*$/u', $rawAnswer, $match)) {
            // El modelo no siguió el protocolo: sin línea FUENTES no se cita nada.
            return [$rawAnswer, []];
        }

        $answer = trim(mb_substr($rawAnswer, 0, -mb_strlen($match[0])));
        $indexes = array_values(array_unique(array_filter(array_map(
            fn (string $part) => (int) trim($part),
            explode(',', $match[1])
        ))));

        return [$answer !== '' ? $answer : $rawAnswer, $indexes];
    }

    /**
     * Unique pages behind the chunks the model actually used, as links.
     * Built server-side from RAG metadata so the model cannot invent sources.
     */
    private function buildSources(array $chunks, array $usedIndexes): array
    {
        $sources = [];
        foreach ($usedIndexes as $number) {
            $chunk = $chunks[$number - 1] ?? null;
            if (!$chunk) {
                continue;
            }
            $sources[$chunk['page_id']] ??= [
                'book' => $chunk['book_name'],
                'page' => $chunk['page_name'],
                'url'  => url('/books/' . $chunk['book_slug'] . '/page/' . $chunk['page_slug']),
            ];
        }

        return array_values($sources);
    }

    private function errorResponse()
    {
        return response()->json([
            'answer'  => trans('entities.ai_chat_error'),
            'sources' => [],
            'no_info' => false,
        ]);
    }
}
