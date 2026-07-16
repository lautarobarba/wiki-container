"""Chunking de páginas y sincronización del índice contra la DB de la wiki.

La sincronización es incremental: primero se diffea la metadata (updated_at)
contra lo ya indexado, y recién después se trae de MySQL el texto y se embebe
ÚNICAMENTE lo nuevo o modificado. Lo que ya forma parte del RAG y no cambió
no se vuelve a leer ni a procesar.
"""

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


def _index_page(page: dict, text: str) -> bool:
    """Embebe y guarda una página. Devuelve False si no tiene contenido."""
    chunks = chunk_text(f"{page['name']}\n{text}")
    if not chunks:
        store.remove_pages([page["id"]])
        return False
    vectors = embeddings.embed_texts(chunks)
    store.upsert_page(page, chunks, vectors)
    return True


def sync(full_rebuild: bool = False) -> dict:
    """Sincroniza el índice con la wiki: agrega páginas nuevas, re-embebe las
    modificadas (updated_at posterior al indexado) y elimina las que ya no
    existen. Con full_rebuild, vacía el índice y regenera todo desde cero."""
    structure = db.fetch_wiki_structure()
    store.replace_metadata(
        structure["shelves"], structure["books"], structure["book_to_shelves"]
    )

    if full_rebuild:
        store.clear_index()

    indexed = store.get_indexed_pages()
    current_ids = {page["id"] for page in structure["pages"]}
    to_remove = [page_id for page_id in indexed if page_id not in current_ids]

    # Diff por updated_at: solo se procesa lo creado o modificado después de
    # la última sincronización; el resto ni se lee de la DB.
    new_pages = [p for p in structure["pages"] if p["id"] not in indexed]
    changed_pages = [
        p for p in structure["pages"]
        if p["id"] in indexed and indexed[p["id"]] != str(p["updated_at"])
    ]

    texts = db.fetch_pages_text([p["id"] for p in new_pages + changed_pages])

    added = updated = 0
    errors: list[str] = []
    for page, counter in [(p, "added") for p in new_pages] + [(p, "updated") for p in changed_pages]:
        try:
            if _index_page(page, texts.get(page["id"], "")):
                if counter == "added":
                    added += 1
                else:
                    updated += 1
        except embeddings.EmbeddingError as exc:
            errors.append(f"Página {page['id']} ({page['name']}): {exc}")
            break  # si OpenAI falla, no tiene sentido seguir intentando

    store.remove_pages(to_remove)

    kind = "rebuild" if full_rebuild else "sync"
    store.log_sync(kind, added, updated, len(to_remove), "; ".join(errors))

    return {
        "added": added,
        "updated": updated,
        "removed": len(to_remove),
        "skipped": len(current_ids) - len(new_pages) - len(changed_pages),
        "errors": errors,
    }
