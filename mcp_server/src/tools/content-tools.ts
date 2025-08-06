import { Tool } from "@modelcontextprotocol/sdk/types.js";
import { BookStackClient } from "../lib/bookstack-client.js";
import { Tag } from "../types/index.js";
import {
  formatApiResponse,
  handleApiError,
  parseInteger,
  parseTags,
  CreateBookSchema,
  UpdateBookSchema,
  CreateChapterSchema,
  UpdateChapterSchema,
  CreatePageSchema,
  UpdatePageSchema,
  CreateShelfSchema,
  UpdateShelfSchema,
  PaginationSchema,
} from "../lib/validation.js";

// Helper function to convert input tags to proper Tag format
function convertTags(
  inputTags?: { name: string; value: string; order?: number }[]
): Tag[] | undefined {
  if (!inputTags) return undefined;
  return inputTags.map((tag) => ({
    name: tag.name,
    value: tag.value,
    order: tag.order ?? 0,
  }));
}

export function createContentTools(client: BookStackClient): Tool[] {
  return [
    // ========== BOOKS ==========
    {
      name: "list_books",
      description: "Get a listing of books visible to the user",
      inputSchema: {
        type: "object",
        properties: {
          page: { type: "number", description: "Page number for pagination" },
          count: { type: "number", description: "Number of items per page" },
          sort: { type: "string", description: "Sort parameter" },
        },
      },
    },
    {
      name: "get_book",
      description:
        "Get details of a specific book including its content structure",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Book ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_book",
      description: "Create a new book in the system",
      inputSchema: {
        type: "object",
        properties: {
          name: {
            type: "string",
            description: "Book name (required, max 255 chars)",
          },
          description: {
            type: "string",
            description: "Book description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Book description (HTML format)",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          default_template_id: {
            type: "number",
            description: "Default template ID for new pages",
          },
        },
        required: ["name"],
      },
    },
    {
      name: "update_book",
      description: "Update an existing book",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Book ID" },
          name: { type: "string", description: "Book name (max 255 chars)" },
          description: {
            type: "string",
            description: "Book description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Book description (HTML format)",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          default_template_id: {
            type: "number",
            description: "Default template ID for new pages",
          },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_book",
      description: "Delete a book (moves to recycle bin)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Book ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "export_book",
      description: "Export a book in various formats",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Book ID" },
          format: {
            type: "string",
            enum: ["html", "pdf", "plaintext", "markdown"],
            description: "Export format",
          },
        },
        required: ["id", "format"],
      },
    },

    // ========== CHAPTERS ==========
    {
      name: "list_chapters",
      description: "Get a listing of chapters visible to the user",
      inputSchema: {
        type: "object",
        properties: {
          page: { type: "number", description: "Page number for pagination" },
          count: { type: "number", description: "Number of items per page" },
          sort: { type: "string", description: "Sort parameter" },
        },
      },
    },
    {
      name: "get_chapter",
      description: "Get details of a specific chapter including its pages",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Chapter ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_chapter",
      description: "Create a new chapter in a book",
      inputSchema: {
        type: "object",
        properties: {
          book_id: { type: "number", description: "Parent book ID" },
          name: {
            type: "string",
            description: "Chapter name (required, max 255 chars)",
          },
          description: {
            type: "string",
            description: "Chapter description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Chapter description (HTML format)",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          priority: { type: "number", description: "Chapter priority/order" },
          default_template_id: {
            type: "number",
            description: "Default template ID for new pages",
          },
        },
        required: ["book_id", "name"],
      },
    },
    {
      name: "update_chapter",
      description: "Update an existing chapter",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Chapter ID" },
          name: { type: "string", description: "Chapter name (max 255 chars)" },
          description: {
            type: "string",
            description: "Chapter description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Chapter description (HTML format)",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          priority: { type: "number", description: "Chapter priority/order" },
          default_template_id: {
            type: "number",
            description: "Default template ID for new pages",
          },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_chapter",
      description: "Delete a chapter (moves to recycle bin)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Chapter ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "export_chapter",
      description: "Export a chapter in various formats",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Chapter ID" },
          format: {
            type: "string",
            enum: ["html", "pdf", "plaintext", "markdown"],
            description: "Export format",
          },
        },
        required: ["id", "format"],
      },
    },

    // ========== PAGES ==========
    {
      name: "list_pages",
      description: "Get a listing of pages visible to the user",
      inputSchema: {
        type: "object",
        properties: {
          page: { type: "number", description: "Page number for pagination" },
          count: { type: "number", description: "Number of items per page" },
          sort: { type: "string", description: "Sort parameter" },
        },
      },
    },
    {
      name: "get_page",
      description: "Get details and content of a specific page",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Page ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_page",
      description: "Create a new page in a book or chapter",
      inputSchema: {
        type: "object",
        properties: {
          book_id: {
            type: "number",
            description: "Parent book ID (required if not in chapter)",
          },
          chapter_id: {
            type: "number",
            description: "Parent chapter ID (required if not directly in book)",
          },
          name: {
            type: "string",
            description: "Page name (required, max 255 chars)",
          },
          html: { type: "string", description: "Page content in HTML format" },
          markdown: {
            type: "string",
            description: "Page content in Markdown format",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          priority: { type: "number", description: "Page priority/order" },
        },
        required: ["name"],
      },
    },
    {
      name: "update_page",
      description: "Update an existing page",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Page ID" },
          book_id: { type: "number", description: "Move to different book ID" },
          chapter_id: {
            type: "number",
            description: "Move to different chapter ID",
          },
          name: { type: "string", description: "Page name (max 255 chars)" },
          html: { type: "string", description: "Page content in HTML format" },
          markdown: {
            type: "string",
            description: "Page content in Markdown format",
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
          priority: { type: "number", description: "Page priority/order" },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_page",
      description: "Delete a page (moves to recycle bin)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Page ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "export_page",
      description: "Export a page in various formats",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Page ID" },
          format: {
            type: "string",
            enum: ["html", "pdf", "plaintext", "markdown"],
            description: "Export format",
          },
        },
        required: ["id", "format"],
      },
    },

    // ========== SHELVES ==========
    {
      name: "list_shelves",
      description: "Get a listing of shelves visible to the user",
      inputSchema: {
        type: "object",
        properties: {
          page: { type: "number", description: "Page number for pagination" },
          count: { type: "number", description: "Number of items per page" },
          sort: { type: "string", description: "Sort parameter" },
        },
      },
    },
    {
      name: "get_shelf",
      description: "Get details of a specific shelf including its books",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Shelf ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_shelf",
      description: "Create a new shelf",
      inputSchema: {
        type: "object",
        properties: {
          name: {
            type: "string",
            description: "Shelf name (required, max 255 chars)",
          },
          description: {
            type: "string",
            description: "Shelf description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Shelf description (HTML format)",
          },
          books: {
            type: "array",
            description: "Array of book IDs to add to shelf",
            items: { type: "number" },
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
        },
        required: ["name"],
      },
    },
    {
      name: "update_shelf",
      description: "Update an existing shelf",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Shelf ID" },
          name: { type: "string", description: "Shelf name (max 255 chars)" },
          description: {
            type: "string",
            description: "Shelf description (plain text)",
          },
          description_html: {
            type: "string",
            description: "Shelf description (HTML format)",
          },
          books: {
            type: "array",
            description: "Array of book IDs (replaces existing books)",
            items: { type: "number" },
          },
          tags: {
            type: "array",
            description: "Array of tags with name and value",
            items: {
              type: "object",
              properties: {
                name: { type: "string" },
                value: { type: "string" },
                order: { type: "number" },
              },
              required: ["name", "value"],
            },
          },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_shelf",
      description: "Delete a shelf (moves to recycle bin)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Shelf ID" },
        },
        required: ["id"],
      },
    },
  ];
}

export async function handleContentTool(
  name: string,
  args: any,
  client: BookStackClient
): Promise<string> {
  try {
    switch (name) {
      // ========== BOOKS ==========
      case "list_books": {
        const params = PaginationSchema.parse(args);
        const result = await client.getBooks(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_book": {
        const id = parseInteger(args.id);
        const result = await client.getBook(id);
        return formatApiResponse(result);
      }

      case "create_book": {
        const validatedData = CreateBookSchema.parse(args);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.createBook(data);
        return formatApiResponse(result);
      }

      case "update_book": {
        const { id, ...updateData } = args;
        const bookId = parseInteger(id);
        const validatedData = UpdateBookSchema.parse(updateData);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.updateBook(bookId, data);
        return formatApiResponse(result);
      }

      case "delete_book": {
        const id = parseInteger(args.id);
        await client.deleteBook(id);
        return `Book ${id} deleted successfully`;
      }

      case "export_book": {
        const id = parseInteger(args.id);
        const format = args.format;

        switch (format) {
          case "html":
            const html = await client.exportBookHtml(id);
            return html;
          case "pdf":
            return "PDF export is binary data - use API directly for file download";
          case "plaintext":
            const text = await client.exportBookPlainText(id);
            return text;
          case "markdown":
            const markdown = await client.exportBookMarkdown(id);
            return markdown;
          default:
            throw new Error(`Unsupported export format: ${format}`);
        }
      }

      // ========== CHAPTERS ==========
      case "list_chapters": {
        const params = PaginationSchema.parse(args);
        const result = await client.getChapters(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_chapter": {
        const id = parseInteger(args.id);
        const result = await client.getChapter(id);
        return formatApiResponse(result);
      }

      case "create_chapter": {
        const validatedData = CreateChapterSchema.parse(args);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.createChapter(data);
        return formatApiResponse(result);
      }

      case "update_chapter": {
        const { id, ...updateData } = args;
        const chapterId = parseInteger(id);
        const validatedData = UpdateChapterSchema.parse(updateData);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.updateChapter(chapterId, data);
        return formatApiResponse(result);
      }

      case "delete_chapter": {
        const id = parseInteger(args.id);
        await client.deleteChapter(id);
        return `Chapter ${id} deleted successfully`;
      }

      case "export_chapter": {
        const id = parseInteger(args.id);
        const format = args.format;

        switch (format) {
          case "html":
            const html = await client.exportChapterHtml(id);
            return html;
          case "pdf":
            return "PDF export is binary data - use API directly for file download";
          case "plaintext":
            const text = await client.exportChapterPlainText(id);
            return text;
          case "markdown":
            const markdown = await client.exportChapterMarkdown(id);
            return markdown;
          default:
            throw new Error(`Unsupported export format: ${format}`);
        }
      }

      // ========== PAGES ==========
      case "list_pages": {
        const params = PaginationSchema.parse(args);
        const result = await client.getPages(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_page": {
        const id = parseInteger(args.id);
        const result = await client.getPage(id);
        return formatApiResponse(result);
      }

      case "create_page": {
        const validatedData = CreatePageSchema.parse(args);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.createPage(data);
        return formatApiResponse(result);
      }

      case "update_page": {
        const { id, ...updateData } = args;
        const pageId = parseInteger(id);
        const validatedData = UpdatePageSchema.parse(updateData);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.updatePage(pageId, data);
        return formatApiResponse(result);
      }

      case "delete_page": {
        const id = parseInteger(args.id);
        await client.deletePage(id);
        return `Page ${id} deleted successfully`;
      }

      case "export_page": {
        const id = parseInteger(args.id);
        const format = args.format;

        switch (format) {
          case "html":
            const html = await client.exportPageHtml(id);
            return html;
          case "pdf":
            return "PDF export is binary data - use API directly for file download";
          case "plaintext":
            const text = await client.exportPagePlainText(id);
            return text;
          case "markdown":
            const markdown = await client.exportPageMarkdown(id);
            return markdown;
          default:
            throw new Error(`Unsupported export format: ${format}`);
        }
      }

      // ========== SHELVES ==========
      case "list_shelves": {
        const params = PaginationSchema.parse(args);
        const result = await client.getShelves(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_shelf": {
        const id = parseInteger(args.id);
        const result = await client.getShelf(id);
        return formatApiResponse(result);
      }

      case "create_shelf": {
        const validatedData = CreateShelfSchema.parse(args);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.createShelf(data);
        return formatApiResponse(result);
      }

      case "update_shelf": {
        const { id, ...updateData } = args;
        const shelfId = parseInteger(id);
        const validatedData = UpdateShelfSchema.parse(updateData);
        const data = {
          ...validatedData,
          tags: convertTags(validatedData.tags),
        };
        const result = await client.updateShelf(shelfId, data);
        return formatApiResponse(result);
      }

      case "delete_shelf": {
        const id = parseInteger(args.id);
        await client.deleteShelf(id);
        return `Shelf ${id} deleted successfully`;
      }

      default:
        throw new Error(`Unknown content tool: ${name}`);
    }
  } catch (error) {
    return handleApiError(error);
  }
}
