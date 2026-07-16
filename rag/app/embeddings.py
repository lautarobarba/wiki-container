"""Cliente mínimo del endpoint de embeddings de OpenAI."""

import httpx

from . import config


class EmbeddingError(Exception):
    pass


def embed_texts(texts: list[str]) -> list[list[float]]:
    """Embebe una lista de textos, en lotes. Lanza EmbeddingError ante
    cualquier fallo (key faltante, API caída, respuesta inesperada)."""
    if not config.OPENAI_API_KEY:
        raise EmbeddingError("OPENAI_API_KEY no está configurada")

    vectors: list[list[float]] = []
    for start in range(0, len(texts), config.EMBEDDING_BATCH_SIZE):
        batch = texts[start : start + config.EMBEDDING_BATCH_SIZE]
        try:
            response = httpx.post(
                "https://api.openai.com/v1/embeddings",
                headers={"Authorization": f"Bearer {config.OPENAI_API_KEY}"},
                json={"model": config.EMBEDDING_MODEL, "input": batch},
                timeout=60,
            )
        except httpx.HTTPError as exc:
            raise EmbeddingError(f"Error de red contra OpenAI: {exc}") from exc

        if response.status_code != 200:
            raise EmbeddingError(
                f"OpenAI respondió {response.status_code}: {response.text[:300]}"
            )

        data = response.json().get("data", [])
        if len(data) != len(batch):
            raise EmbeddingError("OpenAI devolvió una cantidad inesperada de embeddings")

        # OpenAI garantiza el orden, pero se respeta el índice por las dudas.
        data.sort(key=lambda item: item["index"])
        vectors.extend(item["embedding"] for item in data)

    return vectors


def embed_text(text: str) -> list[float]:
    return embed_texts([text])[0]
