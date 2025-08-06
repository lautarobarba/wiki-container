import { Tool } from "@modelcontextprotocol/sdk/types.js";
import { BookStackClient } from "../lib/bookstack-client.js";
import {
  formatApiResponse,
  handleApiError,
  parseInteger,
  PaginationSchema,
} from "../lib/validation.js";

export function createSearchAndUserTools(client: BookStackClient): Tool[] {
  return [
    // ========== SEARCH ==========
    {
      name: "search_all",
      description:
        "Search across all content types (books, chapters, pages, shelves) in BookStack",
      inputSchema: {
        type: "object",
        properties: {
          query: {
            type: "string",
            description: "Search query string (required)",
          },
          page: {
            type: "number",
            description: "Page number for pagination",
          },
          count: {
            type: "number",
            description: "Number of items per page (default 20, max 500)",
          },
        },
        required: ["query"],
      },
    },

    // ========== USERS ==========
    {
      name: "list_users",
      description: "Get a listing of users in the system",
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
      name: "get_user",
      description: "Get details of a specific user",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "User ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_user",
      description: "Create a new user account",
      inputSchema: {
        type: "object",
        properties: {
          name: {
            type: "string",
            description: "Full name of the user (required)",
          },
          email: {
            type: "string",
            description: "Email address (required, must be unique)",
          },
          password: {
            type: "string",
            description: "Password (required, min 8 characters)",
          },
          roles: {
            type: "array",
            description: "Array of role IDs to assign to the user",
            items: { type: "number" },
          },
          language: {
            type: "string",
            description: "User interface language code",
          },
          external_auth_id: {
            type: "string",
            description: "External authentication ID",
          },
          send_invite: {
            type: "boolean",
            description: "Send invitation email to user",
          },
        },
        required: ["name", "email", "password"],
      },
    },
    {
      name: "update_user",
      description: "Update an existing user account",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "User ID" },
          name: { type: "string", description: "Full name of the user" },
          email: {
            type: "string",
            description: "Email address (must be unique)",
          },
          password: {
            type: "string",
            description: "New password (min 8 characters)",
          },
          roles: {
            type: "array",
            description:
              "Array of role IDs to assign to the user (replaces existing)",
            items: { type: "number" },
          },
          language: {
            type: "string",
            description: "User interface language code",
          },
          external_auth_id: {
            type: "string",
            description: "External authentication ID",
          },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_user",
      description: "Delete a user account (requires admin permissions)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "User ID" },
          migrate_ownership_id: {
            type: "number",
            description: "ID of user to transfer ownership of content to",
          },
        },
        required: ["id"],
      },
    },

    // ========== ROLES ==========
    {
      name: "list_roles",
      description: "Get a listing of roles in the system",
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
      name: "get_role",
      description: "Get details of a specific role including permissions",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Role ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "create_role",
      description: "Create a new role",
      inputSchema: {
        type: "object",
        properties: {
          display_name: {
            type: "string",
            description: "Display name for the role (required)",
          },
          description: { type: "string", description: "Role description" },
          external_auth_id: {
            type: "string",
            description: "External authentication ID",
          },
          permissions: {
            type: "array",
            description: "Array of permission names to assign to the role",
            items: { type: "string" },
          },
        },
        required: ["display_name"],
      },
    },
    {
      name: "update_role",
      description: "Update an existing role",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Role ID" },
          display_name: {
            type: "string",
            description: "Display name for the role",
          },
          description: { type: "string", description: "Role description" },
          external_auth_id: {
            type: "string",
            description: "External authentication ID",
          },
          permissions: {
            type: "array",
            description:
              "Array of permission names to assign to the role (replaces existing)",
            items: { type: "string" },
          },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_role",
      description: "Delete a role (users with this role will lose it)",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Role ID" },
          migrate_ownership_id: {
            type: "number",
            description: "ID of role to transfer ownership of content to",
          },
        },
        required: ["id"],
      },
    },

    // ========== ATTACHMENTS & IMAGES ==========
    {
      name: "list_attachments",
      description: "Get a listing of attachments",
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
      name: "get_attachment",
      description: "Get details of a specific attachment",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Attachment ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_attachment",
      description: "Delete an attachment",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Attachment ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "list_images",
      description: "Get a listing of images in the gallery",
      inputSchema: {
        type: "object",
        properties: {
          page: { type: "number", description: "Page number for pagination" },
          count: { type: "number", description: "Number of items per page" },
          sort: { type: "string", description: "Sort parameter" },
          filter_type: {
            type: "string",
            enum: ["gallery", "drawio"],
            description: "Filter images by type",
          },
        },
      },
    },
    {
      name: "get_image",
      description: "Get details of a specific image",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Image ID" },
        },
        required: ["id"],
      },
    },
    {
      name: "update_image",
      description: "Update image details",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Image ID" },
          name: { type: "string", description: "Image name" },
        },
        required: ["id"],
      },
    },
    {
      name: "delete_image",
      description: "Delete an image from the gallery",
      inputSchema: {
        type: "object",
        properties: {
          id: { type: "number", description: "Image ID" },
        },
        required: ["id"],
      },
    },
  ];
}

export async function handleSearchAndUserTool(
  name: string,
  args: any,
  client: BookStackClient
): Promise<string> {
  try {
    switch (name) {
      // ========== SEARCH ==========
      case "search_all": {
        const { query, page, count } = args;
        if (!query || typeof query !== "string") {
          throw new Error("Search query is required and must be a string");
        }

        const params = {
          query,
          page: page ? parseInteger(page) : undefined,
          count: count ? parseInteger(count) : undefined,
        };

        const result = await client.search(query, {
          page: page ? parseInteger(page) : undefined,
          count: count ? parseInteger(count) : undefined,
        });
        return formatApiResponse(result.data, result.total);
      }

      // ========== USERS ==========
      case "list_users": {
        const params = PaginationSchema.parse(args);
        const result = await client.getUsers(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_user": {
        const id = parseInteger(args.id);
        const result = await client.getUser(id);
        return formatApiResponse(result);
      }

      case "create_user": {
        const {
          name,
          email,
          password,
          roles,
          language,
          external_auth_id,
          send_invite,
        } = args;

        if (!name || !email || !password) {
          throw new Error("name, email, and password are required");
        }

        if (password.length < 8) {
          throw new Error("Password must be at least 8 characters long");
        }

        const data = {
          name,
          email,
          password,
          roles: roles || [],
          language,
          external_auth_id,
          send_invite,
        };

        const result = await client.createUser(data);
        return formatApiResponse(result);
      }

      case "update_user": {
        const { id, migrate_ownership_id, ...updateData } = args;
        const userId = parseInteger(id);

        const result = await client.updateUser(userId, updateData);
        return formatApiResponse(result);
      }

      case "delete_user": {
        const { id, migrate_ownership_id } = args;
        const userId = parseInteger(id);
        const migrateId = migrate_ownership_id
          ? parseInteger(migrate_ownership_id)
          : undefined;

        await client.deleteUser(userId, migrateId);
        return `User ${userId} deleted successfully`;
      }

      // ========== ROLES ==========
      case "list_roles": {
        const params = PaginationSchema.parse(args);
        const result = await client.getRoles(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_role": {
        const id = parseInteger(args.id);
        const result = await client.getRole(id);
        return formatApiResponse(result);
      }

      case "create_role": {
        const { display_name, description, external_auth_id, permissions } =
          args;

        if (!display_name) {
          throw new Error("display_name is required");
        }

        const data = {
          display_name,
          description,
          external_auth_id,
          permissions: permissions || [],
        };

        const result = await client.createRole(data);
        return formatApiResponse(result);
      }

      case "update_role": {
        const { id, ...updateData } = args;
        const roleId = parseInteger(id);

        const result = await client.updateRole(roleId, updateData);
        return formatApiResponse(result);
      }

      case "delete_role": {
        const { id, migrate_ownership_id } = args;
        const roleId = parseInteger(id);
        const migrateId = migrate_ownership_id
          ? parseInteger(migrate_ownership_id)
          : undefined;

        await client.deleteRole(roleId);
        return `Role ${roleId} deleted successfully`;
      }

      // ========== ATTACHMENTS ==========
      case "list_attachments": {
        const params = PaginationSchema.parse(args);
        const result = await client.getAttachments(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_attachment": {
        const id = parseInteger(args.id);
        const result = await client.getAttachment(id);
        return formatApiResponse(result);
      }

      case "delete_attachment": {
        const id = parseInteger(args.id);
        await client.deleteAttachment(id);
        return `Attachment ${id} deleted successfully`;
      }

      // ========== IMAGES ==========
      case "list_images": {
        const { filter_type, ...paginationArgs } = args;
        const params = PaginationSchema.parse(paginationArgs);

        const allParams = {
          ...params,
          filter_type,
        };

        const result = await client.getImageGallery(params);
        return formatApiResponse(result.data, result.total);
      }

      case "get_image": {
        const id = parseInteger(args.id);
        const result = await client.getImage(id);
        return formatApiResponse(result);
      }

      case "update_image": {
        const { id, name } = args;
        const imageId = parseInteger(id);

        const result = await client.updateImage(imageId, { name });
        return formatApiResponse(result);
      }

      case "delete_image": {
        const id = parseInteger(args.id);
        await client.deleteImage(id);
        return `Image ${id} deleted successfully`;
      }

      default:
        throw new Error(`Unknown search/user tool: ${name}`);
    }
  } catch (error) {
    return handleApiError(error);
  }
}
