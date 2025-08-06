# 🚀 Guía Rápida: Testear MCP Server en VS Code

## ✅ Estado: Servidor Funcionando Correctamente

El servidor MCP de BookStack está compilado y funcional con **30+ herramientas** disponibles.

## 📋 Opciones de Testeo

### 1. 🧪 Prueba Básica (COMPLETADA ✅)

```bash
npm run test-simple
```

- ✅ Servidor inicia correctamente
- ✅ 30+ herramientas cargadas
- ✅ Sin errores de compilación

### 2. 🔧 Testeo con BookStack Real

#### Paso 1: Configurar credenciales

Edita `test-mcp.js` líneas 11-15:

```javascript
const testConfig = {
  BOOKSTACK_BASE_URL: "http://localhost:8000", // ⬅️ Cambia por tu URL
  BOOKSTACK_TOKEN_ID: "tu_token_id_real", // ⬅️ Tu Token ID
  BOOKSTACK_TOKEN: "tu_token_secreto_real", // ⬅️ Tu Token Secret
};
```

#### Paso 2: Ejecutar pruebas interactivas

```bash
npm run test-interactive
```

### 3. 🔌 Conectar a Claude Desktop

#### Archivo de configuración:

- **Linux**: `~/.config/claude/claude_desktop_config.json`
- **macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows**: `%APPDATA%/Claude/claude_desktop_config.json`

#### Contenido del archivo:

```json
{
  "mcpServers": {
    "bookstack": {
      "command": "node",
      "args": [
        "/home/lautaro/Desarrollo/wiki-desarrollosur/mcp_server/build/index.js"
      ],
      "env": {
        "BOOKSTACK_BASE_URL": "http://localhost:8000",
        "BOOKSTACK_TOKEN_ID": "tu_token_id_aqui",
        "BOOKSTACK_TOKEN": "tu_token_secreto_aqui"
      }
    }
  }
}
```

## 🛠️ Herramientas Disponibles (30+)

### 📚 **Gestión de Contenido**

- **Libros**: `list_books`, `get_book`, `create_book`, `update_book`, `delete_book`, `export_book`
- **Capítulos**: `list_chapters`, `get_chapter`, `create_chapter`, `update_chapter`, `delete_chapter`, `export_chapter`
- **Páginas**: `list_pages`, `get_page`, `create_page`, `update_page`, `delete_page`, `export_page`
- **Estanterías**: `list_shelves`, `get_shelf`, `create_shelf`, `update_shelf`, `delete_shelf`

### 🔍 **Búsqueda y Usuarios**

- **Búsqueda**: `search_all`
- **Usuarios**: `list_users`, `get_user`, `create_user`, `update_user`, `delete_user`
- **Roles**: `list_roles`, `get_role`, `create_role`, `update_role`, `delete_role`

### 📎 **Recursos**

- **Adjuntos**: `list_attachments`, `get_attachment`, `delete_attachment`
- **Imágenes**: `list_images`, `get_image`, `update_image`, `delete_image`

## 🚨 Obtener Tokens de BookStack

### Pasos en BookStack:

1. **Iniciar sesión** en tu instancia BookStack
2. **Ir al perfil** (esquina superior derecha)
3. **Seleccionar "Preferencias"**
4. **Ir a "API Tokens"**
5. **Crear nuevo token** con permisos adecuados
6. **Copiar Token ID y Token Secret**

### Permisos necesarios:

- ✅ Read access to books, chapters, pages
- ✅ Write access (if creating content)
- ✅ User management (if using user tools)

## 🎯 Ejemplos de Uso en Claude

Una vez configurado en Claude Desktop:

```
"Lista todos los libros en mi wiki BookStack"
"Crea un nuevo libro llamado 'Manual de Desarrollo'"
"Busca páginas que contengan 'API documentation'"
"Exporta el libro 'User Guide' en formato Markdown"
"Crea una página con tutorial de TypeScript"
```

## 🐛 Troubleshooting

### Error común: "Missing required configuration"

```bash
❌ Missing required configuration. Please set:
- BOOKSTACK_BASE_URL: Base URL of your BookStack instance
- BOOKSTACK_TOKEN_ID: API Token ID
- BOOKSTACK_TOKEN: API Token Secret
```

**Solución**: Configurar las variables de entorno o editar el archivo de prueba.

### Error: "Cannot connect to BookStack"

**Verificar**:

- ✅ BookStack está ejecutándose
- ✅ URL es correcta (sin barra final)
- ✅ Tokens son válidos
- ✅ Usuario tiene permisos de API

## 📁 Estructura del Proyecto

```
mcp_server/
├── build/              # ✅ Compilado y listo
│   ├── index.js        # ✅ Servidor principal
│   ├── lib/           # ✅ Cliente y validación
│   ├── tools/         # ✅ 30+ herramientas MCP
│   └── types/         # ✅ Tipos TypeScript
├── src/               # 📝 Código fuente
├── test-simple.js     # 🧪 Prueba básica
├── test-mcp.js        # 🔧 Prueba interactiva
└── package.json       # ✅ Scripts configurados
```

## ⚡ Comandos Rápidos

```bash
# Compilar cambios
npm run build

# Prueba rápida
npm run test-simple

# Desarrollo con auto-compilación
npm run dev

# Ejecutar servidor directamente
npm start
```

---

## 🎉 ¡Listo para usar!

El servidor MCP está completamente funcional y listo para conectarse a Claude Desktop o cualquier cliente MCP compatible.
