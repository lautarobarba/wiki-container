#!/usr/bin/env node

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  Tool,
} from "@modelcontextprotocol/sdk/types.js";
import { BookStackClient } from "./lib/bookstack-client.js";
import { BookStackConfig } from "./types/index.js";
import {
  createContentTools,
  handleContentTool,
} from "./tools/content-tools.js";
import {
  createSearchAndUserTools,
  handleSearchAndUserTool,
} from "./tools/search-user-tools.js";

// Configuration from environment variables
const config: BookStackConfig = {
  baseUrl: process.env.BOOKSTACK_BASE_URL || "http://localhost",
  tokenId: process.env.BOOKSTACK_TOKEN_ID || "",
  token: process.env.BOOKSTACK_TOKEN || "",
};

// Validate configuration
if (!config.baseUrl || !config.tokenId || !config.token) {
  console.error("Missing required configuration. Please set:");
  console.error("- BOOKSTACK_BASE_URL: Base URL of your BookStack instance");
  console.error("- BOOKSTACK_TOKEN_ID: API Token ID");
  console.error("- BOOKSTACK_TOKEN: API Token Secret");
  process.exit(1);
}

// Initialize BookStack client
const bookStackClient = new BookStackClient(config);

// Initialize MCP server
const server = new Server(
  {
    name: "bookstack-mcp-server",
    version: "1.0.0",
    description:
      "MCP server for BookStack API integration, enabling AI models to generate and edit wiki content",
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// Combine all tools
const allTools: Tool[] = [
  ...createContentTools(bookStackClient),
  ...createSearchAndUserTools(bookStackClient),
];

// List tools handler
server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: allTools,
  };
});

// Call tool handler
server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    let result: string;

    // Content tools (books, chapters, pages, shelves)
    const contentToolNames = [
      "list_books",
      "get_book",
      "create_book",
      "update_book",
      "delete_book",
      "export_book",
      "list_chapters",
      "get_chapter",
      "create_chapter",
      "update_chapter",
      "delete_chapter",
      "export_chapter",
      "list_pages",
      "get_page",
      "create_page",
      "update_page",
      "delete_page",
      "export_page",
      "list_shelves",
      "get_shelf",
      "create_shelf",
      "update_shelf",
      "delete_shelf",
    ];

    // Search and user tools
    const searchUserToolNames = [
      "search_all",
      "list_users",
      "get_user",
      "create_user",
      "update_user",
      "delete_user",
      "list_roles",
      "get_role",
      "create_role",
      "update_role",
      "delete_role",
      "list_attachments",
      "get_attachment",
      "delete_attachment",
      "list_images",
      "get_image",
      "update_image",
      "delete_image",
    ];

    if (contentToolNames.includes(name)) {
      result = await handleContentTool(name, args, bookStackClient);
    } else if (searchUserToolNames.includes(name)) {
      result = await handleSearchAndUserTool(name, args, bookStackClient);
    } else {
      throw new Error(`Unknown tool: ${name}`);
    }

    return {
      content: [
        {
          type: "text",
          text: result,
        },
      ],
    };
  } catch (error) {
    const errorMessage =
      error instanceof Error ? error.message : "An unknown error occurred";

    return {
      content: [
        {
          type: "text",
          text: `Error executing tool "${name}": ${errorMessage}`,
        },
      ],
      isError: true,
    };
  }
});

// Enhanced error handling
process.on("uncaughtException", (error) => {
  console.error("Uncaught Exception:", error);
  process.exit(1);
});

process.on("unhandledRejection", (reason, promise) => {
  console.error("Unhandled Rejection at:", promise, "reason:", reason);
  process.exit(1);
});

// Start the server
async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);

  // Keep the process alive
  console.error("BookStack MCP Server started successfully");
  console.error(`Connected to BookStack at: ${config.baseUrl}`);
  console.error("Available tools:");
  allTools.forEach((tool) => {
    console.error(`  - ${tool.name}: ${tool.description}`);
  });
}

main().catch((error) => {
  console.error("Failed to start server:", error);
  process.exit(1);
});
