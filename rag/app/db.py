"""Lecturas (solo SELECT) sobre la base MySQL de BookStack."""

import pymysql

from . import config


def _connect():
    return pymysql.connect(
        host=config.DB_HOST,
        port=config.DB_PORT,
        user=config.DB_USERNAME,
        password=config.DB_PASSWORD,
        database=config.DB_DATABASE,
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        connect_timeout=5,
    )


def fetch_wiki_structure():
    """Devuelve la estructura actual de la wiki SIN el contenido de las
    páginas: sistemas (shelves), manuales (books) con los sistemas a los que
    pertenecen, y metadata de páginas publicadas (id, nombre, updated_at).
    El texto se pide aparte, solo para las páginas que cambiaron."""
    with _connect() as conn:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT id, name, slug FROM bookshelves WHERE deleted_at IS NULL"
            )
            shelves = cur.fetchall()

            cur.execute(
                "SELECT bookshelf_id, book_id FROM bookshelves_books"
            )
            shelf_books = cur.fetchall()

            cur.execute(
                "SELECT id, name, slug FROM books WHERE deleted_at IS NULL"
            )
            books = cur.fetchall()

            cur.execute(
                """
                SELECT id, book_id, name, slug, updated_at
                FROM pages
                WHERE deleted_at IS NULL AND draft = 0 AND template = 0
                """
            )
            pages = cur.fetchall()

    book_ids = {book["id"] for book in books}
    shelf_ids = {shelf["id"] for shelf in shelves}
    book_to_shelves: dict[int, list[int]] = {}
    for row in shelf_books:
        if row["book_id"] in book_ids and row["bookshelf_id"] in shelf_ids:
            book_to_shelves.setdefault(row["book_id"], []).append(row["bookshelf_id"])

    return {
        "shelves": shelves,
        "books": books,
        "book_to_shelves": book_to_shelves,
        "pages": [page for page in pages if page["book_id"] in book_ids],
    }


def fetch_pages_text(page_ids: list[int]) -> dict[int, str]:
    """Texto plano de las páginas indicadas (solo las que van a procesarse)."""
    if not page_ids:
        return {}
    with _connect() as conn:
        with conn.cursor() as cur:
            placeholders = ",".join(["%s"] * len(page_ids))
            cur.execute(
                f"SELECT id, text FROM pages WHERE id IN ({placeholders})",
                page_ids,
            )
            rows = cur.fetchall()
    return {row["id"]: row["text"] or "" for row in rows}


def check_connection() -> bool:
    try:
        with _connect() as conn:
            with conn.cursor() as cur:
                cur.execute("SELECT 1")
        return True
    except Exception:
        return False
