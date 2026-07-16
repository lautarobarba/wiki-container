# Wiki RAG

Servicio de RAG (*Retrieval-Augmented Generation*) que indexa el contenido de la wiki BookStack y le da al chat IA de la wiki la capacidad de responder **solo** con información real de los manuales, citando las fuentes que usó.

## Arquitectura

```
┌─────────────┐  1. pregunta   ┌──────────────────┐  3. /query (Basic auth)  ┌─────────────┐
│  Navegador   │ ────────────▶ │     wiki_app      │ ───────────────────────▶ │  wiki_rag    │
│ (widget chat)│               │ (BookStack + PHP) │ ◀─────────────────────── │  (FastAPI)   │
│              │ ◀──────────── │  AiChatController │  4. chunks relevantes    │              │
└─────────────┘  6. respuesta  └──────────────────┘                          └──────┬───────┘
                                        │                                          │
                                        │ 5. chat completion                       │ embeddings /
                                        ▼                                          ▼ lectura de contenido
                                 ┌─────────────┐                    ┌─────────────────────────┐
                                 │ OpenAI API  │ ◀───────────────── │  MySQL wiki_db (SELECT)  │
                                 │ gpt-4o-mini │     (solo en sync) │  SQLite rag.sqlite       │
                                 └─────────────┘                    └─────────────────────────┘
```

Componentes (`app/`):

| Módulo | Rol |
|---|---|
| `main.py` | API FastAPI: `/query`, `/health`, login por sesión y panel `/admin`. |
| `db.py` | Lecturas (solo `SELECT`) sobre la MySQL de BookStack: sistemas, manuales y páginas publicadas. |
| `indexer.py` | Chunking del texto y sincronización incremental del índice. |
| `embeddings.py` | Cliente del endpoint de embeddings de OpenAI (`text-embedding-3-small`), con batching. |
| `store.py` | Persistencia en SQLite (`data/rag.sqlite`) y búsqueda por similitud coseno. |
| `templates/` | Panel de administración (login + dashboard, Jinja2). |

## Cómo funciona

### Indexado (sincronización)

1. Se lee la **estructura** de la wiki desde MySQL: sistemas (shelves), manuales (books), y metadata de páginas publicadas (sin borradores ni plantillas) — **sin el contenido**.
2. Se diffea contra lo ya indexado usando `pages.updated_at`:
   - página nueva → se indexa;
   - página con `updated_at` distinto al indexado → se re-indexa;
   - página que ya no existe en la wiki → se elimina del índice;
   - página sin cambios → **no se lee ni se reprocesa** (no consume API).
3. Solo para las páginas nuevas/modificadas se trae el texto plano, se parte en *chunks* de ~1200 caracteres (solapamiento de 200, cortando en límites de párrafo) y se embebe cada chunk con OpenAI.
4. Chunks + vectores + metadata (manual, sistema, slugs) quedan en SQLite. El mapa manual→sistemas se refresca en cada sync.

La sincronización se dispara desde el panel: **Sincronizar** (incremental, lo descripto arriba) o **Regenerar todo** (vacía el índice y re-embebe todo; usar solo si se sospecha corrupción o se cambió el modelo de embeddings).

### Consulta (la usa el chat de la wiki)

`POST /query` con `{question, allowed_book_ids, top_k}` (HTTP Basic con las credenciales del panel):

1. Se embebe la pregunta con el mismo modelo.
2. Similitud coseno (numpy, brute-force — sobra para el tamaño de una wiki) contra los chunks **solo de los manuales permitidos**.
3. Devuelve los `top_k` chunks con su metadata y score.

**Los permisos los decide siempre BookStack**: el PHP del chat manda en `allowed_book_ids` únicamente los manuales visibles para el usuario que pregunta. El RAG nunca resuelve visibilidad por su cuenta. La respuesta final al usuario la genera `gpt-4o-mini` desde el PHP de la wiki, instruido para usar solo estos chunks y citar cuáles usó.

### Panel de administración

`http://localhost:${RAG_PORT}/admin` — login con usuario/contraseña de `.env` (sesión por cookie firmada). Muestra estado de conexiones, totales del índice, manuales indexados por sistema, historial de sincronizaciones, y los botones de sync.

## Configuración (`.env` de la raíz del repo)

| Variable | Descripción |
|---|---|
| `OPENAI_API_KEY` | Key de OpenAI (sirve una service account). Usada para embeddings (acá) y chat (en PHP). |
| `RAG_PORT` | Puerto publicado del servicio (default 8090). |
| `RAG_ADMIN_USER` / `RAG_ADMIN_PASSWORD` | Credenciales del panel y del endpoint `/query`. |
| `DB_USERNAME` / `DB_PASSWORD` / `DB_DATABASE` | Los mismos que usa BookStack; el servicio se conecta a `wiki_db` por la red interna de compose. |

## Operación

- Levantar: `docker compose up -d --build wiki_rag` (desde la raíz del repo).
- El índice persiste en `rag/data/rag.sqlite` (bind mount, gitignored). Borrar ese archivo equivale a un "Regenerar todo" en la próxima sync.
- `GET /health` (sin auth) devuelve el estado del servicio y de la conexión a la DB.
- Si OpenAI devuelve error (falta de crédito, etc.), la sincronización se corta y el error queda visible en el panel y en el historial.
