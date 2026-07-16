"""Persistencia del índice en SQLite y búsqueda por similitud coseno.

Los embeddings se guardan como BLOB float32 y la búsqueda es brute-force con
numpy: para el tamaño de una wiki de homelab (miles de chunks) responde en
milisegundos y evita depender de una base vectorial aparte.
"""

import json
import sqlite3
import threading

import numpy as np

from . import config

_lock = threading.Lock()


def _connect() -> sqlite3.Connection:
    conn = sqlite3.connect(config.SQLITE_PATH)
    conn.row_factory = sqlite3.Row
    return conn


def init_db() -> None:
    with _connect() as conn:
        conn.executescript(
            """
            CREATE TABLE IF NOT EXISTS pages_indexed (
                page_id INTEGER PRIMARY KEY,
                book_id INTEGER NOT NULL,
                page_name TEXT NOT NULL,
                page_slug TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS books_meta (
                book_id INTEGER PRIMARY KEY,
                book_name TEXT NOT NULL,
                book_slug TEXT NOT NULL,
                shelf_ids TEXT NOT NULL DEFAULT '[]'
            );
            CREATE TABLE IF NOT EXISTS shelves_meta (
                shelf_id INTEGER PRIMARY KEY,
                shelf_name TEXT NOT NULL,
                shelf_slug TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS chunks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                page_id INTEGER NOT NULL,
                book_id INTEGER NOT NULL,
                position INTEGER NOT NULL,
                text TEXT NOT NULL,
                embedding BLOB NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_chunks_page ON chunks(page_id);
            CREATE INDEX IF NOT EXISTS idx_chunks_book ON chunks(book_id);
            CREATE TABLE IF NOT EXISTS sync_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ran_at TEXT NOT NULL DEFAULT (datetime('now')),
                kind TEXT NOT NULL,
                added INTEGER NOT NULL,
                updated INTEGER NOT NULL,
                removed INTEGER NOT NULL,
                detail TEXT NOT NULL DEFAULT ''
            );
            """
        )


def get_indexed_pages() -> dict[int, str]:
    """page_id -> updated_at indexado."""
    with _connect() as conn:
        rows = conn.execute("SELECT page_id, updated_at FROM pages_indexed").fetchall()
    return {row["page_id"]: row["updated_at"] for row in rows}


def replace_metadata(shelves: list[dict], books: list[dict], book_to_shelves: dict[int, list[int]]) -> None:
    with _lock, _connect() as conn:
        conn.execute("DELETE FROM shelves_meta")
        conn.executemany(
            "INSERT INTO shelves_meta (shelf_id, shelf_name, shelf_slug) VALUES (?, ?, ?)",
            [(s["id"], s["name"], s["slug"]) for s in shelves],
        )
        conn.execute("DELETE FROM books_meta")
        conn.executemany(
            "INSERT INTO books_meta (book_id, book_name, book_slug, shelf_ids) VALUES (?, ?, ?, ?)",
            [
                (b["id"], b["name"], b["slug"], json.dumps(book_to_shelves.get(b["id"], [])))
                for b in books
            ],
        )


def upsert_page(page: dict, chunks: list[str], embeddings: list[list[float]]) -> None:
    with _lock, _connect() as conn:
        conn.execute("DELETE FROM chunks WHERE page_id = ?", (page["id"],))
        conn.executemany(
            "INSERT INTO chunks (page_id, book_id, position, text, embedding) VALUES (?, ?, ?, ?, ?)",
            [
                (
                    page["id"],
                    page["book_id"],
                    position,
                    text,
                    np.asarray(vector, dtype=np.float32).tobytes(),
                )
                for position, (text, vector) in enumerate(zip(chunks, embeddings))
            ],
        )
        conn.execute(
            """
            INSERT INTO pages_indexed (page_id, book_id, page_name, page_slug, updated_at)
            VALUES (?, ?, ?, ?, ?)
            ON CONFLICT(page_id) DO UPDATE SET
                book_id = excluded.book_id,
                page_name = excluded.page_name,
                page_slug = excluded.page_slug,
                updated_at = excluded.updated_at
            """,
            (page["id"], page["book_id"], page["name"], page["slug"], str(page["updated_at"])),
        )


def remove_pages(page_ids: list[int]) -> None:
    if not page_ids:
        return
    with _lock, _connect() as conn:
        placeholders = ",".join("?" * len(page_ids))
        conn.execute(f"DELETE FROM chunks WHERE page_id IN ({placeholders})", page_ids)
        conn.execute(f"DELETE FROM pages_indexed WHERE page_id IN ({placeholders})", page_ids)


def clear_index() -> None:
    with _lock, _connect() as conn:
        conn.execute("DELETE FROM chunks")
        conn.execute("DELETE FROM pages_indexed")


def log_sync(kind: str, added: int, updated: int, removed: int, detail: str = "") -> None:
    with _connect() as conn:
        conn.execute(
            "INSERT INTO sync_log (kind, added, updated, removed, detail) VALUES (?, ?, ?, ?, ?)",
            (kind, added, updated, removed, detail),
        )


def search(query_vector: list[float], allowed_book_ids: list[int], top_k: int) -> list[dict]:
    """Chunks más similares a la consulta dentro de los manuales permitidos."""
    if not allowed_book_ids:
        return []

    with _connect() as conn:
        placeholders = ",".join("?" * len(allowed_book_ids))
        rows = conn.execute(
            f"""
            SELECT c.page_id, c.book_id, c.text, c.embedding,
                   p.page_name, p.page_slug, b.book_name, b.book_slug
            FROM chunks c
            JOIN pages_indexed p ON p.page_id = c.page_id
            JOIN books_meta b ON b.book_id = c.book_id
            WHERE c.book_id IN ({placeholders})
            """,
            allowed_book_ids,
        ).fetchall()

    if not rows:
        return []

    matrix = np.frombuffer(b"".join(row["embedding"] for row in rows), dtype=np.float32)
    matrix = matrix.reshape(len(rows), -1)
    query = np.asarray(query_vector, dtype=np.float32)

    norms = np.linalg.norm(matrix, axis=1) * np.linalg.norm(query)
    norms[norms == 0] = 1e-9
    scores = matrix @ query / norms

    order = np.argsort(scores)[::-1][:top_k]
    return [
        {
            "page_id": rows[i]["page_id"],
            "book_id": rows[i]["book_id"],
            "page_name": rows[i]["page_name"],
            "page_slug": rows[i]["page_slug"],
            "book_name": rows[i]["book_name"],
            "book_slug": rows[i]["book_slug"],
            "text": rows[i]["text"],
            "score": float(scores[i]),
        }
        for i in order
    ]


def get_admin_overview() -> dict:
    """Datos agregados para el panel: sistemas con sus manuales indexados."""
    with _connect() as conn:
        shelves = conn.execute("SELECT * FROM shelves_meta ORDER BY shelf_name").fetchall()
        books = conn.execute("SELECT * FROM books_meta ORDER BY book_name").fetchall()
        page_counts = {
            row["book_id"]: row["pages"]
            for row in conn.execute(
                "SELECT book_id, COUNT(*) AS pages FROM pages_indexed GROUP BY book_id"
            )
        }
        chunk_counts = {
            row["book_id"]: row["chunks"]
            for row in conn.execute(
                "SELECT book_id, COUNT(*) AS chunks FROM chunks GROUP BY book_id"
            )
        }
        last_syncs = conn.execute(
            "SELECT * FROM sync_log ORDER BY id DESC LIMIT 10"
        ).fetchall()
        totals = conn.execute(
            "SELECT (SELECT COUNT(*) FROM pages_indexed) AS pages, (SELECT COUNT(*) FROM chunks) AS chunks"
        ).fetchone()

    books_by_shelf: dict[int, list[dict]] = {}
    orphan_books: list[dict] = []
    for book in books:
        entry = {
            "book_id": book["book_id"],
            "book_name": book["book_name"],
            "pages": page_counts.get(book["book_id"], 0),
            "chunks": chunk_counts.get(book["book_id"], 0),
        }
        shelf_ids = json.loads(book["shelf_ids"])
        if shelf_ids:
            for shelf_id in shelf_ids:
                books_by_shelf.setdefault(shelf_id, []).append(entry)
        else:
            orphan_books.append(entry)

    return {
        "shelves": [
            {
                "shelf_id": shelf["shelf_id"],
                "shelf_name": shelf["shelf_name"],
                "books": books_by_shelf.get(shelf["shelf_id"], []),
            }
            for shelf in shelves
        ],
        "orphan_books": orphan_books,
        "last_syncs": [dict(row) for row in last_syncs],
        "total_pages": totals["pages"],
        "total_chunks": totals["chunks"],
    }
