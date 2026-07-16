"""Chunking de páginas y sincronización del índice contra la DB de la wiki."""

from . import config, db, embeddings, store


def chunk_text(text: str) -> list[str]:
    """Parte el texto plano de una página en chunks de ~CHUNK_SIZE caracteres
    con solapamiento, cortando preferentemente en límites de párrafo."""
    text = text.strip()
    if not text:
        return []
    if len(text) <= config.CHUNK_SIZE:
        return [text]

    chunks = []
    start = 0
    while start < len(text):
        end = min(start + config.CHUNK_SIZE, len(text))
        if end < len(text):
            # Retrocede hasta el último salto de párrafo/línea dentro del chunk
            # para no cortar oraciones al medio (sin achicarlo demasiado).
            for separator in ("\n\n", "\n", ". "):
                cut = text.rfind(separator, start + config.CHUNK_SIZE // 2, end)
                if cut != -1:
                    end = cut + len(separator)
                    break
        chunks.append(text[start:end].strip())
        if end >= len(text):
            break
        start = max(end - config.CHUNK_OVERLAP, start + 1)

    return [chunk for chunk in chunks if chunk]


def _index_page(page: dict) -> bool:
    """Embebe y guarda una página. Devuelve False si no tiene contenido."""
    chunks = chunk_text(f"{page['name']}\n{page['text'] or ''}")
    if not chunks:
        store.remove_pages([page["id"]])
        return False
    vectors = embeddings.embed_texts(chunks)
    store.upsert_page(page, chunks, vectors)
    return True


def sync(full_rebuild: bool = False) -> dict:
    """Sincroniza el índice con la wiki: agrega páginas nuevas, re-embebe las
    modificadas y elimina las que ya no existen. Con full_rebuild, vacía el
    índice y regenera todo desde cero."""
    snapshot = db.fetch_wiki_snapshot()
    store.replace_metadata(
        snapshot["shelves"], snapshot["books"], snapshot["book_to_shelves"]
    )

    if full_rebuild:
        store.clear_index()

    indexed = store.get_indexed_pages()
    current_ids = {page["id"] for page in snapshot["pages"]}

    to_remove = [page_id for page_id in indexed if page_id not in current_ids]
    added = updated = 0
    errors: list[str] = []

    for page in snapshot["pages"]:
        known = indexed.get(page["id"])
        if known is None:
            is_new = True
        elif known != str(page["updated_at"]):
            is_new = False
        else:
            continue

        try:
            if _index_page(page):
                if is_new:
                    added += 1
                else:
                    updated += 1
        except embeddings.EmbeddingError as exc:
            errors.append(f"Página {page['id']} ({page['name']}): {exc}")
            break  # si OpenAI falla, no tiene sentido seguir intentando

    store.remove_pages(to_remove)

    kind = "rebuild" if full_rebuild else "sync"
    detail = "; ".join(errors)
    store.log_sync(kind, added, updated, len(to_remove), detail)

    return {
        "added": added,
        "updated": updated,
        "removed": len(to_remove),
        "errors": errors,
    }
