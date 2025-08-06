// BookStack API Types and Interfaces

export interface BookStackConfig {
  baseUrl: string;
  token: string;
  tokenId: string;
}

export interface ApiResponse<T> {
  data: T;
  total?: number;
}

export interface PaginationParams {
  page?: number;
  count?: number;
  sort?: string;
  filter?: Record<string, string>;
}

// User types
export interface User {
  id: number;
  name: string;
  email: string;
  slug: string;
  created_at: string;
  updated_at: string;
  external_auth_id?: string;
  last_activity_at?: string;
  profile_url?: string;
  edit_url?: string;
  avatar_url?: string;
  roles?: Role[];
}

export interface Role {
  id: number;
  display_name: string;
  description?: string;
  system_name?: string;
  external_auth_id?: string;
  mfa_enforced?: boolean;
  created_at: string;
  updated_at: string;
  users_count?: number;
  permissions_count?: number;
  permissions?: string[];
  users?: User[];
}

// Content types
export interface Tag {
  name: string;
  value: string;
  order: number;
}

export interface Image {
  id: number;
  name: string;
  url: string;
  path?: string;
  type?: string;
  uploaded_to?: number;
  created_at: string;
  updated_at: string;
  created_by?: number | User;
  updated_by?: number | User;
}

export interface Shelf {
  id: number;
  name: string;
  slug: string;
  description: string;
  description_html?: string;
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  owned_by: number | User;
  tags?: Tag[];
  cover?: Image;
  books?: Book[];
}

export interface Book {
  id: number;
  name: string;
  slug: string;
  description: string;
  description_html?: string;
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  owned_by: number | User;
  default_template_id?: number;
  tags?: Tag[];
  cover?: Image;
  contents?: (Chapter | Page)[];
}

export interface Chapter {
  id: number;
  book_id: number;
  name: string;
  slug: string;
  description: string;
  description_html?: string;
  priority?: number;
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  owned_by: number | User;
  default_template_id?: number;
  book_slug?: string;
  tags?: Tag[];
  pages?: Page[];
  type?: "chapter";
}

export interface Page {
  id: number;
  book_id: number;
  chapter_id?: number;
  name: string;
  slug: string;
  html?: string;
  raw_html?: string;
  markdown?: string;
  priority?: number;
  draft: boolean;
  template: boolean;
  revision_count: number;
  editor: "wysiwyg" | "markdown";
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  owned_by: number | User;
  book_slug?: string;
  tags?: Tag[];
  type?: "page";
}

export interface Attachment {
  id: number;
  name: string;
  extension: string;
  uploaded_to: number;
  external: boolean;
  order: number;
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  content?: string;
  links?: {
    html: string;
    markdown: string;
  };
}

export interface ImageGallery {
  id: number;
  name: string;
  url: string;
  path: string;
  type: "gallery" | "drawio";
  uploaded_to: number;
  created_at: string;
  updated_at: string;
  created_by: number | User;
  updated_by: number | User;
  thumbs?: {
    gallery: string;
    display: string;
  };
  content?: {
    html: string;
    markdown: string;
  };
}

// Search types
export interface SearchResult {
  id: number;
  name: string;
  slug: string;
  type: "bookshelf" | "book" | "chapter" | "page";
  url: string;
  created_at: string;
  updated_at: string;
  book?: {
    id: number;
    name: string;
    slug: string;
  };
  chapter?: {
    id: number;
    name: string;
    slug: string;
  };
  preview_html?: {
    name: string;
    content: string;
  };
  tags?: Tag[];
}

// Permission types
export interface ContentPermissions {
  owner: User;
  role_permissions: RolePermission[];
  fallback_permissions: FallbackPermissions;
}

export interface RolePermission {
  role_id: number;
  view: boolean;
  create: boolean;
  update: boolean;
  delete: boolean;
  role: {
    id: number;
    display_name: string;
  };
}

export interface FallbackPermissions {
  inheriting: boolean;
  view?: boolean;
  create?: boolean;
  update?: boolean;
  delete?: boolean;
}

// Audit log types
export interface AuditLogEntry {
  id: number;
  type: string;
  detail: string;
  user_id: number;
  loggable_id?: number;
  loggable_type?: string;
  ip: string;
  created_at: string;
  user: {
    id: number;
    name: string;
    slug: string;
  };
}

// Recycle bin types
export interface RecycleBinItem {
  id: number;
  deleted_by: number;
  created_at: string;
  updated_at: string;
  deletable_type: "page" | "book" | "chapter" | "bookshelf";
  deletable_id: number;
  deletable: any; // Type varies based on deletable_type
}

// Request/Response types for creating/updating
export interface CreateBookRequest {
  name: string;
  description?: string;
  description_html?: string;
  tags?: Tag[];
  default_template_id?: number;
}

export interface CreateChapterRequest {
  book_id: number;
  name: string;
  description?: string;
  description_html?: string;
  tags?: Tag[];
  priority?: number;
  default_template_id?: number;
}

export interface CreatePageRequest {
  book_id?: number;
  chapter_id?: number;
  name: string;
  html?: string;
  markdown?: string;
  tags?: Tag[];
  priority?: number;
}

export interface CreateShelfRequest {
  name: string;
  description?: string;
  description_html?: string;
  books?: number[];
  tags?: Tag[];
}

export interface CreateUserRequest {
  name: string;
  email: string;
  external_auth_id?: string;
  language?: string;
  password?: string;
  roles?: number[];
  send_invite?: boolean;
}

export interface CreateRoleRequest {
  display_name: string;
  description?: string;
  mfa_enforced?: boolean;
  external_auth_id?: string;
  permissions?: string[];
}

export interface CreateAttachmentRequest {
  name: string;
  uploaded_to: number;
  link?: string;
}

export interface CreateImageRequest {
  type: "gallery" | "drawio";
  uploaded_to: number;
  name?: string;
}
