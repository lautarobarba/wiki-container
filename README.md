# Wiki Bookstack

## Wiki para homelab

Documentar: $ sudo chown -R www-data: bookstack/

## Instalación

Hay que crear y cambiar permisos de uploads a www-data

```bash
$ sudo mkdir -p bookstack/public/uploads
$ sudo mkdir -p bookstack/storage/uploads
$ sudo mkdir -p bookstack/logs
$ sudo chown -R www-data:www-data bookstack
```

## Chat IA + RAG

Hay un chat IA flotante (abajo a la derecha) que aparece solo cuando lo que se está viendo pertenece a un sistema, y responde únicamente sobre los manuales de ese sistema. El retrieval lo hace el servicio `wiki_rag` (carpeta `rag/`), que indexa el contenido de la wiki con embeddings de OpenAI en SQLite; las respuestas las genera `gpt-4o-mini` de OpenAI.

Para que funcione:

1. Completar en `.env`: `OPENAI_API_KEY` (https://platform.openai.com/api-keys) y `RAG_ADMIN_PASSWORD`.
2. Levantar el stack: `docker compose up -d --build` (el servicio `wiki_rag` se buildea desde `rag/`).
3. Entrar al panel de administración del RAG en `http://localhost:8090/admin` (credenciales `RAG_ADMIN_USER`/`RAG_ADMIN_PASSWORD`) y ejecutar la primera sincronización.

El panel permite **Sincronizar** (agrega manuales nuevos, re-indexa modificados y quita eliminados, comparando `updated_at` de las páginas) y **Regenerar todo** (vacía y re-embebe todo el índice). El índice persiste en `rag/data/rag.sqlite`. Si el RAG está caído o falta la key, el chat muestra un mensaje de error amigable.

## Usuario por default

```bash
USER: admin@admin.com
PASSWD: password
```

## Actualizacion de APP_URL

La APP_URL se guarda estáticamente en las siguientes tablas:

    - bookstack.settings
    - bookstack.images

Por lo que hay que actualizar manualmente en caso de cambiarla.

```sql
-- Cambio localhost:8000 por nueva_url
SELECT url FROM bookstack.images;
UPDATE bookstack.images SET url = REPLACE(url, 'http://localhost:8000', 'http://nueva_url');

SELECT * FROM bookstack.settings s ;
UPDATE bookstack.settings SET value = REPLACE(value, 'http://localhost:8000', 'http://nueva_url');

COMMIT;
```
