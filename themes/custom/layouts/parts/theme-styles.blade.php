{{--
    Capa de diseño "moderno" del theme custom. Se incluye desde layouts/base.blade.php
    después de custom-styles, así puede pisar los estilos de dist/styles.css y usar
    las variables de color (--color-primary, etc.) que definen los settings.
--}}
<style>
    :root {
        --wiki-radius-s: 8px;
        --wiki-radius-m: 12px;
        --wiki-radius-l: 16px;
        --wiki-border: #dfe6ef;
        --wiki-surface: #ffffff;
        --wiki-bg: #f2f5f9;
        --wiki-shadow-s: 0 1px 2px rgba(16, 30, 54, 0.06), 0 1px 6px rgba(16, 30, 54, 0.05);
        --wiki-shadow-m: 0 4px 12px rgba(16, 30, 54, 0.08), 0 2px 4px rgba(16, 30, 54, 0.05);
        --wiki-shadow-l: 0 12px 28px rgba(16, 30, 54, 0.14), 0 4px 10px rgba(16, 30, 54, 0.08);
        --wiki-primary-soft: color-mix(in srgb, var(--color-primary) 9%, transparent);
        --wiki-ring: 0 0 0 3px color-mix(in srgb, var(--color-primary) 25%, transparent);
    }
    html.dark-mode {
        --wiki-border: rgba(255, 255, 255, 0.09);
        --wiki-surface: #1c2126;
        --wiki-bg: #14181c;
        --wiki-shadow-s: 0 1px 2px rgba(0, 0, 0, 0.35);
        --wiki-shadow-m: 0 4px 12px rgba(0, 0, 0, 0.4);
        --wiki-shadow-l: 0 12px 28px rgba(0, 0, 0, 0.55);
        --wiki-primary-soft: color-mix(in srgb, var(--color-primary) 18%, transparent);
    }

    /* ---- Base ---- */
    body {
        background: var(--wiki-bg);
        -webkit-font-smoothing: antialiased;
    }
    h1, h2, h3, h4 {
        font-weight: 600;
        letter-spacing: -0.015em;
    }
    h1 { font-size: 1.8em; }
    h2 { font-size: 1.6em; }
    h3 { font-size: 1.4em; }

    /* Movido a la izquierda: el rincón inferior derecho lo ocupa el chat IA */
    .back-to-top {
        right: auto;
        left: 24px;
        border-radius: var(--wiki-radius-m);
    }

    /* ---- Header ---- */
    #header.primary-background {
        background: linear-gradient(120deg,
            color-mix(in srgb, var(--color-primary) 100%, #ffffff 0%),
            color-mix(in srgb, var(--color-primary) 78%, #001a2e));
        box-shadow: 0 2px 12px rgba(9, 30, 51, 0.25);
    }
    #header .links a {
        border-radius: 999px;
        transition: background-color 0.15s ease;
    }
    #header .links a:hover {
        background-color: rgba(255, 255, 255, 0.14);
        text-decoration: none;
    }
    #header .search-box {
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }
    #header .search-box:focus-within {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.45);
    }
    #header .search-box input {
        background: transparent;
        border: none;
        color: #ffffff;
    }
    #header .search-box input::placeholder {
        color: rgba(255, 255, 255, 0.75);
    }
    #header .search-box button svg {
        fill: rgba(255, 255, 255, 0.85);
    }

    /* ---- Cards y superficies ---- */
    .card,
    html.dark-mode .card {
        background-color: var(--wiki-surface);
        border: 1px solid var(--wiki-border);
        border-radius: var(--wiki-radius-m);
        box-shadow: var(--wiki-shadow-s);
    }
    .card .card-title {
        letter-spacing: -0.01em;
    }
    .content-wrap.card {
        border-radius: var(--wiki-radius-l);
    }

    .grid-card {
        border: 1px solid var(--wiki-border);
        border-radius: var(--wiki-radius-m);
        overflow: hidden;
        box-shadow: var(--wiki-shadow-s);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .grid-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--wiki-shadow-m);
        border-color: color-mix(in srgb, var(--color-primary) 35%, var(--wiki-border));
        text-decoration: none;
    }

    .entity-list-item {
        border-radius: var(--wiki-radius-s);
        transition: background-color 0.15s ease;
    }

    /* ---- Botones ---- */
    .button {
        border-radius: var(--wiki-radius-s);
        font-weight: 500;
        transition: background-color 0.15s ease, box-shadow 0.15s ease, transform 0.1s ease;
    }
    .button:not(.outline):hover {
        box-shadow: var(--wiki-shadow-s);
        transform: translateY(-1px);
    }
    .button:active {
        transform: translateY(0);
    }

    /* ---- Formularios ---- */
    #content input[type="text"],
    #content input[type="search"],
    #content input[type="email"],
    #content input[type="number"],
    #content input[type="password"],
    #content input[type="url"],
    #content textarea,
    #content select {
        border-radius: var(--wiki-radius-s);
        border-color: var(--wiki-border);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    #content input:focus,
    #content textarea:focus,
    #content select:focus {
        border-color: var(--color-primary);
        box-shadow: var(--wiki-ring);
        outline: none;
    }

    /* ---- Menús y navegación ---- */
    .dropdown-menu {
        border-radius: var(--wiki-radius-m);
        border: 1px solid var(--wiki-border);
        box-shadow: var(--wiki-shadow-l);
        overflow: hidden;
    }
    .icon-list-item {
        border-radius: var(--wiki-radius-s);
        transition: background-color 0.12s ease;
    }
    .icon-list-item:hover {
        background-color: var(--wiki-primary-soft);
        text-decoration: none;
    }
    .sidebar-page-list .selected-page,
    .sidebar-page-list a.selected-page:hover {
        background-color: var(--wiki-primary-soft);
        border-radius: var(--wiki-radius-s);
    }

    .tag-item {
        border-radius: 999px;
        overflow: hidden;
    }

    /* ---- Scrollbars ---- */
    * {
        scrollbar-width: thin;
        scrollbar-color: color-mix(in srgb, var(--color-primary) 30%, transparent) transparent;
    }
</style>
