# Testear el Servidor MCP en VS Code

## Métodos para testear el servidor MCP de BookStack

### 1. 🧪 Prueba Simple (Verificar que inicia)

```bash
npm run test-simple
```

Este comando:

- Inicia el servidor MCP con configuración de prueba
- Verifica que se carga sin errores
- Muestra las herramientas disponibles
- Se cierra automáticamente después de 3 segundos

### 2. 🔧 Prueba Interactiva (Testear herramientas)

Primero, edita `test-mcp.js` y configura tus credenciales reales:

```javascript
const testConfig = {
  BOOKSTACK_BASE_URL: "http://localhost:8000", // Tu URL de BookStack
  BOOKSTACK_TOKEN_ID: "tu_token_id_real", // Tu Token ID
  BOOKSTACK_TOKEN: "tu_token_secreto_real", // Tu Token Secret
};
```

Luego ejecuta:

```bash
npm run test-interactive
```

Este script te permite:

- Verificar conectividad con BookStack
- Probar herramientas específicas (listar libros, páginas, etc.)
- Crear contenido de prueba
- Menú interactivo para múltiples pruebas

### 3. 🔌 Integración con Claude Desktop (Uso Real)

#### Paso 1: Obtener tokens de BookStack

1. Ve a tu BookStack → Usuario → Preferencias → API Tokens
2. Crea un nuevo token con permisos necesarios
3. Guarda el Token ID y Token Secret

#### Paso 2: Configurar Claude Desktop

Edita tu archivo de configuración de Claude Desktop (ubicación depende del OS):

**macOS**: `~/Library/Application Support/Claude/claude_desktop_config.json`
**Windows**: `%APPDATA%/Claude/claude_desktop_config.json`
**Linux**: `~/.config/claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "bookstack": {
      "command": "node",
      "args": ["/ruta/completa/a/tu/mcp_server/build/index.js"],
      "env": {
        "BOOKSTACK_BASE_URL": "http://localhost:8000",
        "BOOKSTACK_TOKEN_ID": "tu_token_id_real",
        "BOOKSTACK_TOKEN": "tu_token_secreto_real"
      }
    }
  }
}
```

#### Paso 3: Reiniciar Claude Desktop

Cierra y abre Claude Desktop para que detecte la nueva configuración.

### 4. 📋 Verificación en Terminal (Desarrollo)

Para desarrollo y debugging, puedes ejecutar directamente:

```bash
# Compilar si hay cambios
npm run build

# Ejecutar con variables de entorno
BOOKSTACK_BASE_URL="http://localhost:8000" \
BOOKSTACK_TOKEN_ID="tu_token_id" \
BOOKSTACK_TOKEN="tu_token_secreto" \
npm start
```

### 5. 🐛 Debugging

#### Ver logs detallados:

```bash
# En una terminal, ejecuta el servidor
npm start

# En otra terminal, puedes enviar solicitudes JSON-RPC manualmente
echo '{"jsonrpc":"2.0","id":1,"method":"tools/list"}' | npm start
```

#### Problemas comunes:

1. **Error de compilación TypeScript**:

   ```bash
   npm run build
   ```

2. **Error de permisos en tokens**:

   - Verifica que el usuario tenga permisos de API en BookStack
   - Verifica que los tokens no hayan expirado

3. **Error de conectividad**:
   - Verifica que BookStack esté ejecutándose
   - Verifica la URL base (sin barra final)

### 6. 🔍 Herramientas Disponibles

Una vez que el servidor esté funcionando, tendrás acceso a 30+ herramientas:

**Contenido:**

- `list_books`, `create_book`, `update_book`, etc.
- `list_pages`, `create_page`, `update_page`, etc.
- `list_chapters`, `create_chapter`, etc.
- `list_shelves`, `create_shelf`, etc.

**Búsqueda:**

- `search_all` - Buscar en todo el contenido

**Usuarios y Roles:**

- `list_users`, `create_user`, `update_user`, etc.
- `list_roles`, `create_role`, etc.

**Recursos:**

- `list_attachments`, `list_images`, etc.

### 7. 💡 Ejemplos de Uso en Claude

Una vez configurado en Claude Desktop, puedes pedirle:

- "Lista todos los libros en mi wiki"
- "Crea un nuevo libro llamado 'Guía de Desarrollo'"
- "Busca todas las páginas que contengan 'TypeScript'"
- "Crea una página con un tutorial de JavaScript"
- "Exporta el libro 'Manual de Usuario' en formato Markdown"

### 8. 📝 Notas de Desarrollo

- Los archivos TypeScript están en `src/`
- Los archivos compilados están en `build/`
- Usa `npm run dev` para compilación automática durante desarrollo
- Los logs del servidor aparecen en stderr (se muestran en la consola)
