import os

# API de OpenAI (embeddings). La misma key se usa desde PHP para el chat.
OPENAI_API_KEY = os.environ.get("OPENAI_API_KEY", "")
EMBEDDING_MODEL = os.environ.get("RAG_EMBEDDING_MODEL", "text-embedding-3-small")
EMBEDDING_DIMENSIONS = 1536
EMBEDDING_BATCH_SIZE = 100

# Conexión a la DB MySQL de la wiki (mismas vars que usa BookStack en compose).
DB_HOST = os.environ.get("DB_HOST", "wiki_db").split(":")[0]
DB_PORT = int(os.environ.get("DB_PORT", "3306"))
DB_USERNAME = os.environ.get("DB_USERNAME", "")
DB_PASSWORD = os.environ.get("DB_PASSWORD", "")
DB_DATABASE = os.environ.get("DB_DATABASE", "bookstack")

# Credenciales del panel de administración y del endpoint /query.
ADMIN_USER = os.environ.get("RAG_ADMIN_USER", "admin")
ADMIN_PASSWORD = os.environ.get("RAG_ADMIN_PASSWORD", "")

# Índice persistente.
DATA_DIR = os.environ.get("RAG_DATA_DIR", "/app/data")
SQLITE_PATH = os.path.join(DATA_DIR, "rag.sqlite")

# Chunking de páginas.
CHUNK_SIZE = 1200
CHUNK_OVERLAP = 200
