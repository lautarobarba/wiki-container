import os
import secrets

from fastapi import Depends, FastAPI, HTTPException, Request, status
from fastapi.responses import RedirectResponse
from fastapi.security import HTTPBasic, HTTPBasicCredentials
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel, Field

from . import config, db, embeddings, indexer, store

app = FastAPI(title="Wiki RAG", docs_url=None, redoc_url=None)
templates = Jinja2Templates(directory=os.path.join(os.path.dirname(__file__), "templates"))
security = HTTPBasic()


@app.on_event("startup")
def startup() -> None:
    os.makedirs(config.DATA_DIR, exist_ok=True)
    store.init_db()


def require_auth(credentials: HTTPBasicCredentials = Depends(security)) -> str:
    """HTTP Basic con las credenciales de .env, tanto para el panel como
    para /query (BookStack llama con las mismas)."""
    password_ok = config.ADMIN_PASSWORD and secrets.compare_digest(
        credentials.password.encode(), config.ADMIN_PASSWORD.encode()
    )
    user_ok = secrets.compare_digest(
        credentials.username.encode(), config.ADMIN_USER.encode()
    )
    if not (user_ok and password_ok):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas",
            headers={"WWW-Authenticate": "Basic"},
        )
    return credentials.username


@app.get("/health")
def health() -> dict:
    return {"status": "ok", "database": db.check_connection()}


class QueryRequest(BaseModel):
    question: str = Field(min_length=1, max_length=2000)
    allowed_book_ids: list[int]
    top_k: int = Field(default=8, ge=1, le=20)


@app.post("/query")
def query(body: QueryRequest, _: str = Depends(require_auth)) -> dict:
    if not body.allowed_book_ids:
        return {"chunks": []}
    try:
        vector = embeddings.embed_text(body.question)
    except embeddings.EmbeddingError as exc:
        raise HTTPException(status_code=502, detail=str(exc))

    return {"chunks": store.search(vector, body.allowed_book_ids, body.top_k)}


@app.get("/admin")
def admin(request: Request, _: str = Depends(require_auth)):
    overview = store.get_admin_overview()
    return templates.TemplateResponse(
        request,
        "dashboard.html",
        {
            "overview": overview,
            "db_ok": db.check_connection(),
            "openai_configured": bool(config.OPENAI_API_KEY),
            "result": None,
        },
    )


def _run_sync(request: Request, full_rebuild: bool):
    try:
        result = indexer.sync(full_rebuild=full_rebuild)
    except Exception as exc:  # DB caída, etc.: mostrarlo en el panel
        result = {"added": 0, "updated": 0, "removed": 0, "errors": [str(exc)]}

    overview = store.get_admin_overview()
    return templates.TemplateResponse(
        request,
        "dashboard.html",
        {
            "overview": overview,
            "db_ok": db.check_connection(),
            "openai_configured": bool(config.OPENAI_API_KEY),
            "result": result,
        },
    )


@app.post("/admin/sync")
def admin_sync(request: Request, _: str = Depends(require_auth)):
    return _run_sync(request, full_rebuild=False)


@app.post("/admin/rebuild")
def admin_rebuild(request: Request, _: str = Depends(require_auth)):
    return _run_sync(request, full_rebuild=True)


@app.get("/")
def root() -> RedirectResponse:
    return RedirectResponse("/admin")
