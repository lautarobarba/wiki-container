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

        return response()->json([
            'answer'  => $result['answer'],
            'sources' => $result['sources'],
            'no_info' => $result['no_info'],
        ]);
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

        $systemPrompt = "Sos el asistente IA de una wiki interna y respondés sobre los manuales del sistema \"{$shelfName}\". "
            . "Seguí estas reglas en orden:\n"
            . "1. Si el mensaje del usuario es un saludo, agradecimiento o charla trivial (no una pregunta), respondé cordialmente en una frase, "
            . "invitando a preguntar sobre los manuales del sistema. No menciones el CONTEXTO.\n"
            . "2. Si es una pregunta, respondela ÚNICAMENTE con la información del CONTEXTO numerado de abajo, sin conocimiento externo ni inventos. "
            . "Tené en cuenta la conversación previa para entender referencias (\"eso\", \"y cómo sigo\", etc.).\n"
            . "3. Si la pregunta no se puede responder con el CONTEXTO, respondé exactamente: \"{$noInfoMessage}\"\n"
            . "4. OBLIGATORIO: terminá SIEMPRE tu respuesta con una última línea con el formato \"FUENTES: \" seguida de los números de los fragmentos "
            . "del CONTEXTO que usaste para responder, separados por coma (ej.: \"FUENTES: 1,3\"). Si no usaste ninguno (saludos, charla, o sin información), "
            . "terminá con \"FUENTES:\" sin números. Nunca cites fragmentos que no aporten a la respuesta.\n"
            . "Respondé siempre en español, claro y conciso.\n\nCONTEXTO:\n{$contextText}";

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
                    'temperature' => 0.2,
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
