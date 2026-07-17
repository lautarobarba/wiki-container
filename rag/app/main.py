import os
import secrets

from fastapi import Depends, FastAPI, Form, HTTPException, Request, status
from fastapi.responses import RedirectResponse
from fastapi.security import HTTPBasic, HTTPBasicCredentials
from fastapi.templating import Jinja2Templates
from pydantic import BaseModel, Field
from starlette.middleware.sessions import SessionMiddleware

from . import config, db, embeddings, indexer, store

app = FastAPI(title="Wiki RAG", docs_url=None, redoc_url=None)
app.add_middleware(SessionMiddleware, secret_key=config.SESSION_SECRET, https_only=False)
templates = Jinja2Templates(directory=os.path.join(os.path.dirname(__file__), "templates"))
security = HTTPBasic()


@app.on_event("startup")
def startup() -> None:
    os.makedirs(config.DATA_DIR, exist_ok=True)
    store.init_db()


def _credentials_valid(username: str, password: str) -> bool:
    password_ok = config.ADMIN_PASSWORD and secrets.compare_digest(
        password.encode(), config.ADMIN_PASSWORD.encode()
    )
    user_ok = secrets.compare_digest(username.encode(), config.ADMIN_USER.encode())
    return bool(user_ok and password_ok)


def require_basic_auth(credentials: HTTPBasicCredentials = Depends(security)) -> str:
    """HTTP Basic para /query (lo consume BookStack, no un navegador)."""
    if not _credentials_valid(credentials.username, credentials.password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Credenciales inválidas",
            headers={"WWW-Authenticate": "Basic"},
        )
    return credentials.username


def session_user(request: Request) -> str | None:
    return request.session.get("user")


# --- API interna ---


@app.get("/health")
def health() -> dict:
    return {"status": "ok", "database": db.check_connection()}


class QueryRequest(BaseModel):
    question: str = Field(min_length=1, max_length=2000)
    allowed_book_ids: list[int]
    top_k: int = Field(default=8, ge=1, le=20)


@app.post("/query")
def query(body: QueryRequest, _: str = Depends(require_basic_auth)) -> dict:
    if not body.allowed_book_ids:
        return {"chunks": []}
    try:
        vector = embeddings.embed_text(body.question)
    except embeddings.EmbeddingError as exc:
        raise HTTPException(status_code=502, detail=str(exc))

    return {"chunks": store.search(vector, body.allowed_book_ids, body.top_k)}


class LogRequest(BaseModel):
    user_id: int | None = None
    user_name: str = Field(default="", max_length=255)
    shelf_id: int | None = None
    shelf_name: str = Field(default="", max_length=255)
    question: str = Field(min_length=1, max_length=4000)
    answer: str = Field(default="", max_length=8000)
    sources: list[dict] = Field(default_factory=list)
    blocked: bool = False


@app.post("/log")
def log(body: LogRequest, _: str = Depends(require_basic_auth)) -> dict:
    """Registra un intercambio del chatbot (lo llama BookStack, no un navegador)."""
    store.log_conversation(
        user_id=body.user_id,
        user_name=body.user_name,
        shelf_id=body.shelf_id,
        shelf_name=body.shelf_name,
        question=body.question,
        answer=body.answer,
        sources=body.sources,
        blocked=body.blocked,
    )
    return {"ok": True}


# --- Login del panel ---


@app.get("/login")
def login_page(request: Request):
    if session_user(request):
        return RedirectResponse("/admin", status_code=303)
    return templates.TemplateResponse(request, "login.html", {"error": None})


@app.post("/login")
def login(request: Request, username: str = Form(...), password: str = Form(...)):
    if not _credentials_valid(username, password):
        return templates.TemplateResponse(
            request,
            "login.html",
            {"error": "Usuario o contraseña incorrectos."},
            status_code=401,
        )
    request.session["user"] = username
    return RedirectResponse("/admin", status_code=303)


@app.post("/logout")
def logout(request: Request):
    request.session.clear()
    return RedirectResponse("/login", status_code=303)


# --- Panel de administración (requiere sesión) ---


def _admin_context(request: Request, result: dict | None = None) -> dict:
    return {
        "overview": store.get_admin_overview(),
        "db_ok": db.check_connection(),
        "openai_configured": bool(config.OPENAI_API_KEY),
        "result": result,
        "user": session_user(request),
    }


@app.get("/admin")
def admin(request: Request):
    if not session_user(request):
        return RedirectResponse("/login", status_code=303)
    return templates.TemplateResponse(request, "dashboard.html", _admin_context(request))


def _run_sync(request: Request, full_rebuild: bool):
    if not session_user(request):
        return RedirectResponse("/login", status_code=303)
    try:
        result = indexer.sync(full_rebuild=full_rebuild)
    except Exception as exc:  # DB caída, etc.: mostrarlo en el panel
        result = {"added": 0, "updated": 0, "removed": 0, "skipped": 0, "errors": [str(exc)]}

    return templates.TemplateResponse(request, "dashboard.html", _admin_context(request, result))


@app.get("/admin/conversations")
def admin_conversations(request: Request, user_id: int | None = None):
    if not session_user(request):
        return RedirectResponse("/login", status_code=303)
    return templates.TemplateResponse(
        request,
        "conversations.html",
        {
            "user": session_user(request),
            "users": store.get_conversation_users(),
            "conversations": store.get_conversations(user_id=user_id),
            "selected_user_id": user_id,
        },
    )


@app.post("/admin/sync")
def admin_sync(request: Request):
    return _run_sync(request, full_rebuild=False)


@app.post("/admin/rebuild")
def admin_rebuild(request: Request):
    return _run_sync(request, full_rebuild=True)


@app.get("/")
def root() -> RedirectResponse:
    return RedirectResponse("/admin")
