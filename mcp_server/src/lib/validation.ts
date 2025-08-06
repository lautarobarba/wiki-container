import { z } from "zod";

// Validation schemas for MCP tools

export const PaginationSchema = z
  .object({
    page: z.number().optional(),
    count: z.number().optional(),
    sort: z.string().optional(),
  })
  .optional();

export const TagSchema = z.object({
  name: z.string(),
  value: z.string(),
  order: z.number().optional(),
});

export const CreateBookSchema = z.object({
  name: z.string().min(1).max(255),
  description: z.string().optional(),
  description_html: z.string().optional(),
  tags: z.array(TagSchema).optional(),
  default_template_id: z.number().optional(),
});

export const UpdateBookSchema = CreateBookSchema.partial();

export const CreateChapterSchema = z.object({
  book_id: z.number(),
  name: z.string().min(1).max(255),
  description: z.string().optional(),
  description_html: z.string().optional(),
  tags: z.array(TagSchema).optional(),
  priority: z.number().optional(),
  default_template_id: z.number().optional(),
});

export const UpdateChapterSchema = CreateChapterSchema.partial().omit({
  book_id: true,
});

export const CreatePageSchema = z
  .object({
    book_id: z.number().optional(),
    chapter_id: z.number().optional(),
    name: z.string().min(1).max(255),
    html: z.string().optional(),
    markdown: z.string().optional(),
    tags: z.array(TagSchema).optional(),
    priority: z.number().optional(),
  })
  .refine((data) => data.book_id || data.chapter_id, {
    message: "Either book_id or chapter_id must be provided",
  })
  .refine((data) => data.html || data.markdown, {
    message: "Either html or markdown content must be provided",
  });

export const UpdatePageSchema = z.object({
  book_id: z.number().optional(),
  chapter_id: z.number().optional(),
  name: z.string().min(1).max(255).optional(),
  html: z.string().optional(),
  markdown: z.string().optional(),
  tags: z.array(TagSchema).optional(),
  priority: z.number().optional(),
});

export const CreateShelfSchema = z.object({
  name: z.string().min(1).max(255),
  description: z.string().optional(),
  description_html: z.string().optional(),
  books: z.array(z.number()).optional(),
  tags: z.array(TagSchema).optional(),
});

export const UpdateShelfSchema = CreateShelfSchema.partial();

export const CreateUserSchema = z.object({
  name: z.string().min(1).max(100),
  email: z.string().email(),
  external_auth_id: z.string().optional(),
  language: z.string().max(15).optional(),
  password: z.string().min(8).optional(),
  roles: z.array(z.number()).optional(),
  send_invite: z.boolean().optional(),
});

export const UpdateUserSchema = CreateUserSchema.partial();

export const CreateRoleSchema = z.object({
  display_name: z.string().min(3).max(180),
  description: z.string().max(180).optional(),
  mfa_enforced: z.boolean().optional(),
  external_auth_id: z.string().max(180).optional(),
  permissions: z.array(z.string()).optional(),
});

export const UpdateRoleSchema = CreateRoleSchema.partial();

export const CreateAttachmentSchema = z.object({
  name: z.string().min(1).max(255),
  uploaded_to: z.number(),
  link: z.string().optional(),
});

export const UpdateAttachmentSchema = CreateAttachmentSchema.partial().omit({
  uploaded_to: true,
});

export const CreateImageSchema = z.object({
  type: z.enum(["gallery", "drawio"]),
  uploaded_to: z.number(),
  name: z.string().max(180).optional(),
});

export const UpdateImageSchema = z.object({
  name: z.string().max(180).optional(),
});

export const SearchSchema = z.object({
  query: z.string().min(1),
  page: z.number().optional(),
  count: z.number().optional(),
});

export const ContentPermissionsSchema = z.object({
  owner_id: z.number().optional(),
  role_permissions: z
    .array(
      z.object({
        role_id: z.number(),
        view: z.boolean(),
        create: z.boolean(),
        update: z.boolean(),
        delete: z.boolean(),
      })
    )
    .optional(),
  fallback_permissions: z
    .object({
      inheriting: z.boolean(),
      view: z.boolean().optional(),
      create: z.boolean().optional(),
      update: z.boolean().optional(),
      delete: z.boolean().optional(),
    })
    .optional(),
});

// Helper functions for common operations
export function formatApiResponse<T>(data: T, total?: number): string {
  const response = total !== undefined ? { data, total } : data;
  return JSON.stringify(response, null, 2);
}

export function parseInteger(value: unknown): number {
  if (typeof value === "number") return value;
  if (typeof value === "string") {
    const parsed = parseInt(value, 10);
    if (isNaN(parsed)) throw new Error(`Invalid integer: ${value}`);
    return parsed;
  }
  throw new Error(`Expected integer, got ${typeof value}`);
}

export function parseTags(
  tags: unknown
): Array<{ name: string; value: string; order?: number }> {
  if (!tags) return [];
  if (typeof tags === "string") {
    try {
      tags = JSON.parse(tags);
    } catch {
      throw new Error("Invalid JSON for tags");
    }
  }
  return z.array(TagSchema).parse(tags);
}

export function parseBoolean(value: unknown): boolean {
  if (typeof value === "boolean") return value;
  if (typeof value === "string") {
    const lower = value.toLowerCase();
    if (lower === "true" || lower === "1") return true;
    if (lower === "false" || lower === "0") return false;
  }
  throw new Error(`Invalid boolean: ${value}`);
}

export function handleApiError(error: any): string {
  if (error.response) {
    // API returned an error response
    const status = error.response.status;
    const message = error.response.data?.message || error.response.statusText;
    return `API Error ${status}: ${message}`;
  } else if (error.request) {
    // Network error
    return `Network Error: Unable to reach BookStack API`;
  } else {
    // Other error
    return `Error: ${error.message}`;
  }
}

export function validateContentType(contentType: string): string {
  const validTypes = ["page", "book", "chapter", "bookshelf"];
  if (!validTypes.includes(contentType)) {
    throw new Error(
      `Invalid content type. Must be one of: ${validTypes.join(", ")}`
    );
  }
  return contentType;
}
