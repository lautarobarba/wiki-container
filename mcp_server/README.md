# BookStack MCP Server

Un servidor del Protocolo de Contexto de Modelo (MCP) que proporciona una interfaz completa para la API de BookStack, permitiendo que modelos de IA generen y editen contenido de wiki.

## Características

Este servidor MCP proporciona herramientas para:

### 📚 Gestión de Contenido

- **Libros**: Crear, leer, actualizar, eliminar y exportar libros
- **Capítulos**: Gestionar capítulos dentro de libros
- **Páginas**: Crear y editar páginas con contenido HTML o Markdown
- **Estanterías**: Organizar libros en colecciones

### 🔍 Búsqueda y Descubrimiento

- **Búsqueda Global**: Buscar en todo el contenido de BookStack
- **Navegación**: Explorar la estructura del contenido

### 👥 Gestión de Usuarios y Permisos

- **Usuarios**: Crear, actualizar y gestionar cuentas de usuario
- **Roles**: Configurar roles y permisos
- **Autenticación**: Soporte para autenticación externa

### 📎 Recursos Multimedia

- **Adjuntos**: Gestionar archivos adjuntos
- **Imágenes**: Administrar la galería de imágenes

### 📤 Exportación

- **Múltiples Formatos**: Exportar contenido en HTML, PDF, texto plano y Markdown

## Instalación

### Prerrequisitos

- Node.js 18 o superior
- Una instancia de BookStack en funcionamiento
- Token de API de BookStack

### Configuración

1. **Instalar dependencias:**

```bash
npm install
```

2. **Configurar variables de entorno:**

```bash
export BOOKSTACK_BASE_URL="https://tu-bookstack.example.com"
export BOOKSTACK_TOKEN_ID="tu_token_id"
export BOOKSTACK_TOKEN="tu_token_secreto"
```

3. **Compilar el proyecto:**

```bash
npm run build
```

### Obtener Tokens de API de BookStack

1. Inicia sesión en tu instancia de BookStack
2. Ve a tu perfil de usuario (esquina superior derecha)
3. Selecciona "Preferencias" → "API Tokens"
4. Crea un nuevo token con los permisos necesarios
5. Guarda el Token ID y el Token Secret

## Uso

### Ejecutar el Servidor

```bash
npm start
```

O ejecutar directamente:

```bash
node dist/index.js
```

### Integración con Cliente MCP

El servidor se comunica a través de stdio y está diseñado para ser usado con clientes MCP como Claude Desktop.

#### Configuración de Claude Desktop

Agrega esta configuración a tu archivo de configuración de Claude Desktop:

```json
{
  "mcpServers": {
    "bookstack": {
      "command": "node",
      "args": ["/ruta/a/tu/mcp_server/dist/index.js"],
      "env": {
        "BOOKSTACK_BASE_URL": "https://tu-bookstack.example.com",
        "BOOKSTACK_TOKEN_ID": "tu_token_id",
        "BOOKSTACK_TOKEN": "tu_token_secreto"
      }
    }
  }
}
```

## Herramientas Disponibles

### Gestión de Libros

- `list_books` - Listar todos los libros
- `get_book` - Obtener detalles de un libro específico
- `create_book` - Crear un nuevo libro
- `update_book` - Actualizar un libro existente
- `delete_book` - Eliminar un libro
- `export_book` - Exportar libro en varios formatos

### Gestión de Capítulos

- `list_chapters` - Listar capítulos
- `get_chapter` - Obtener detalles de un capítulo
- `create_chapter` - Crear un nuevo capítulo
- `update_chapter` - Actualizar un capítulo
- `delete_chapter` - Eliminar un capítulo
- `export_chapter` - Exportar capítulo

### Gestión de Páginas

- `list_pages` - Listar páginas
- `get_page` - Obtener contenido de una página
- `create_page` - Crear una nueva página
- `update_page` - Actualizar contenido de página
- `delete_page` - Eliminar una página
- `export_page` - Exportar página

### Gestión de Estanterías

- `list_shelves` - Listar estanterías
- `get_shelf` - Obtener detalles de una estantería
- `create_shelf` - Crear una nueva estantería
- `update_shelf` - Actualizar una estantería
- `delete_shelf` - Eliminar una estantería

### Búsqueda

- `search_all` - Buscar en todo el contenido

### Gestión de Usuarios

- `list_users` - Listar usuarios
- `get_user` - Obtener detalles de usuario
- `create_user` - Crear nuevo usuario
- `update_user` - Actualizar usuario
- `delete_user` - Eliminar usuario

### Gestión de Roles

- `list_roles` - Listar roles
- `get_role` - Obtener detalles de rol
- `create_role` - Crear nuevo rol
- `update_role` - Actualizar rol
- `delete_role` - Eliminar rol

### Gestión de Recursos

- `list_attachments` - Listar adjuntos
- `get_attachment` - Obtener detalles de adjunto
- `delete_attachment` - Eliminar adjunto
- `list_images` - Listar imágenes
- `get_image` - Obtener detalles de imagen
- `update_image` - Actualizar imagen
- `delete_image` - Eliminar imagen

## Ejemplos de Uso

### Crear un Nuevo Libro

```javascript
// A través del cliente MCP
await mcpClient.callTool("create_book", {
  name: "Guía de Desarrollo",
  description: "Una guía completa para el desarrollo de software",
  tags: [
    { name: "categoria", value: "desarrollo" },
    { name: "nivel", value: "intermedio" },
  ],
});
```

### Crear una Página con Contenido

```javascript
await mcpClient.callTool("create_page", {
  book_id: 1,
  name: "Introducción a TypeScript",
  markdown: `# Introducción a TypeScript

TypeScript es un lenguaje de programación desarrollado por Microsoft...

## Características principales

- Tipado estático
- Compatibilidad con JavaScript
- Herramientas de desarrollo avanzadas
`,
  tags: [
    { name: "lenguaje", value: "typescript" },
    { name: "tema", value: "introduccion" },
  ],
});
```

### Buscar Contenido

```javascript
await mcpClient.callTool("search_all", {
  query: "typescript desarrollo",
  count: 10,
});
```

## Seguridad

- Todas las operaciones requieren un token de API válido de BookStack
- Los permisos se manejan a través del sistema de roles de BookStack
- Las validaciones de entrada utilizan schemas Zod para mayor seguridad
- Manejo robusto de errores para evitar exposición de información sensible

## Desarrollo

### Estructura del Proyecto

```
src/
├── index.ts              # Punto de entrada del servidor MCP
├── types/                # Definiciones de tipos TypeScript
│   └── index.ts
├── lib/                  # Utilidades y cliente API
│   ├── bookstack-client.ts
│   └── validation.ts
└── tools/                # Implementación de herramientas MCP
    ├── content-tools.ts
    └── search-user-tools.ts
```

### Scripts Disponibles

- `npm run build` - Compilar TypeScript a JavaScript
- `npm run dev` - Ejecutar en modo desarrollo con watch
- `npm start` - Ejecutar el servidor compilado
- `npm test` - Ejecutar tests (si están configurados)

### Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para problemas y preguntas:

1. Verifica la documentación de la API de BookStack
2. Revisa los logs de error del servidor MCP
3. Crea un issue en el repositorio del proyecto

## Changelog

### v1.0.0

- Implementación inicial del servidor MCP
- Soporte completo para la API de BookStack
- Herramientas para gestión de contenido, usuarios y búsqueda
- Validación robusta con Zod
- Exportación en múltiples formatos
