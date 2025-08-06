import axios, { AxiosInstance, AxiosResponse } from "axios";
import FormData from "form-data";
import {
  BookStackConfig,
  ApiResponse,
  PaginationParams,
  User,
  Role,
  Book,
  Chapter,
  Page,
  Shelf,
  Attachment,
  ImageGallery,
  SearchResult,
  ContentPermissions,
  AuditLogEntry,
  RecycleBinItem,
  CreateBookRequest,
  CreateChapterRequest,
  CreatePageRequest,
  CreateShelfRequest,
  CreateUserRequest,
  CreateRoleRequest,
  CreateAttachmentRequest,
  CreateImageRequest,
} from "../types/index.js";

export class BookStackClient {
  private api: AxiosInstance;

  constructor(config: BookStackConfig) {
    this.api = axios.create({
      baseURL: `${config.baseUrl}/api`,
      headers: {
        Authorization: `Token ${config.tokenId}:${config.token}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      },
    });
  }

  // Helper method for GET requests with pagination
  private async get<T>(
    endpoint: string,
    params?: PaginationParams
  ): Promise<ApiResponse<T[]>> {
    const response: AxiosResponse<ApiResponse<T[]>> = await this.api.get(
      endpoint,
      { params }
    );
    return response.data;
  }

  // Helper method for POST requests
  private async post<T>(endpoint: string, data?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.post(endpoint, data);
    return response.data;
  }

  // Helper method for PUT requests
  private async put<T>(endpoint: string, data?: any): Promise<T> {
    const response: AxiosResponse<T> = await this.api.put(endpoint, data);
    return response.data;
  }

  // Helper method for DELETE requests
  private async delete(endpoint: string): Promise<void> {
    await this.api.delete(endpoint);
  }

  // Helper method for multipart form data
  private async postMultipart<T>(
    endpoint: string,
    formData: FormData
  ): Promise<T> {
    const response: AxiosResponse<T> = await this.api.post(endpoint, formData, {
      headers: {
        ...formData.getHeaders(),
      },
    });
    return response.data;
  }

  // ========== BOOKS ==========
  async getBooks(params?: PaginationParams): Promise<ApiResponse<Book[]>> {
    return this.get<Book>("/books", params);
  }

  async getBook(id: number): Promise<Book> {
    const response: AxiosResponse<Book> = await this.api.get(`/books/${id}`);
    return response.data;
  }

  async createBook(data: CreateBookRequest): Promise<Book> {
    return this.post<Book>("/books", data);
  }

  async updateBook(
    id: number,
    data: Partial<CreateBookRequest>
  ): Promise<Book> {
    return this.put<Book>(`/books/${id}`, data);
  }

  async deleteBook(id: number): Promise<void> {
    return this.delete(`/books/${id}`);
  }

  async exportBookHtml(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/books/${id}/export/html`
    );
    return response.data;
  }

  async exportBookPdf(id: number): Promise<Buffer> {
    const response: AxiosResponse<Buffer> = await this.api.get(
      `/books/${id}/export/pdf`,
      {
        responseType: "arraybuffer",
      }
    );
    return response.data;
  }

  async exportBookPlainText(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/books/${id}/export/plaintext`
    );
    return response.data;
  }

  async exportBookMarkdown(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/books/${id}/export/markdown`
    );
    return response.data;
  }

  // ========== CHAPTERS ==========
  async getChapters(
    params?: PaginationParams
  ): Promise<ApiResponse<Chapter[]>> {
    return this.get<Chapter>("/chapters", params);
  }

  async getChapter(id: number): Promise<Chapter> {
    const response: AxiosResponse<Chapter> = await this.api.get(
      `/chapters/${id}`
    );
    return response.data;
  }

  async createChapter(data: CreateChapterRequest): Promise<Chapter> {
    return this.post<Chapter>("/chapters", data);
  }

  async updateChapter(
    id: number,
    data: Partial<CreateChapterRequest>
  ): Promise<Chapter> {
    return this.put<Chapter>(`/chapters/${id}`, data);
  }

  async deleteChapter(id: number): Promise<void> {
    return this.delete(`/chapters/${id}`);
  }

  async exportChapterHtml(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/chapters/${id}/export/html`
    );
    return response.data;
  }

  async exportChapterPdf(id: number): Promise<Buffer> {
    const response: AxiosResponse<Buffer> = await this.api.get(
      `/chapters/${id}/export/pdf`,
      {
        responseType: "arraybuffer",
      }
    );
    return response.data;
  }

  async exportChapterPlainText(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/chapters/${id}/export/plaintext`
    );
    return response.data;
  }

  async exportChapterMarkdown(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/chapters/${id}/export/markdown`
    );
    return response.data;
  }

  // ========== PAGES ==========
  async getPages(params?: PaginationParams): Promise<ApiResponse<Page[]>> {
    return this.get<Page>("/pages", params);
  }

  async getPage(id: number): Promise<Page> {
    const response: AxiosResponse<Page> = await this.api.get(`/pages/${id}`);
    return response.data;
  }

  async createPage(data: CreatePageRequest): Promise<Page> {
    return this.post<Page>("/pages", data);
  }

  async updatePage(
    id: number,
    data: Partial<CreatePageRequest>
  ): Promise<Page> {
    return this.put<Page>(`/pages/${id}`, data);
  }

  async deletePage(id: number): Promise<void> {
    return this.delete(`/pages/${id}`);
  }

  async exportPageHtml(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/pages/${id}/export/html`
    );
    return response.data;
  }

  async exportPagePdf(id: number): Promise<Buffer> {
    const response: AxiosResponse<Buffer> = await this.api.get(
      `/pages/${id}/export/pdf`,
      {
        responseType: "arraybuffer",
      }
    );
    return response.data;
  }

  async exportPagePlainText(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/pages/${id}/export/plaintext`
    );
    return response.data;
  }

  async exportPageMarkdown(id: number): Promise<string> {
    const response: AxiosResponse<string> = await this.api.get(
      `/pages/${id}/export/markdown`
    );
    return response.data;
  }

  // ========== SHELVES ==========
  async getShelves(params?: PaginationParams): Promise<ApiResponse<Shelf[]>> {
    return this.get<Shelf>("/shelves", params);
  }

  async getShelf(id: number): Promise<Shelf> {
    const response: AxiosResponse<Shelf> = await this.api.get(`/shelves/${id}`);
    return response.data;
  }

  async createShelf(data: CreateShelfRequest): Promise<Shelf> {
    return this.post<Shelf>("/shelves", data);
  }

  async updateShelf(
    id: number,
    data: Partial<CreateShelfRequest>
  ): Promise<Shelf> {
    return this.put<Shelf>(`/shelves/${id}`, data);
  }

  async deleteShelf(id: number): Promise<void> {
    return this.delete(`/shelves/${id}`);
  }

  // ========== USERS ==========
  async getUsers(params?: PaginationParams): Promise<ApiResponse<User[]>> {
    return this.get<User>("/users", params);
  }

  async getUser(id: number): Promise<User> {
    const response: AxiosResponse<User> = await this.api.get(`/users/${id}`);
    return response.data;
  }

  async createUser(data: CreateUserRequest): Promise<User> {
    return this.post<User>("/users", data);
  }

  async updateUser(
    id: number,
    data: Partial<CreateUserRequest>
  ): Promise<User> {
    return this.put<User>(`/users/${id}`, data);
  }

  async deleteUser(id: number, migrateOwnershipId?: number): Promise<void> {
    const data = migrateOwnershipId
      ? { migrate_ownership_id: migrateOwnershipId }
      : undefined;
    await this.api.delete(`/users/${id}`, { data });
  }

  // ========== ROLES ==========
  async getRoles(params?: PaginationParams): Promise<ApiResponse<Role[]>> {
    return this.get<Role>("/roles", params);
  }

  async getRole(id: number): Promise<Role> {
    const response: AxiosResponse<Role> = await this.api.get(`/roles/${id}`);
    return response.data;
  }

  async createRole(data: CreateRoleRequest): Promise<Role> {
    return this.post<Role>("/roles", data);
  }

  async updateRole(
    id: number,
    data: Partial<CreateRoleRequest>
  ): Promise<Role> {
    return this.put<Role>(`/roles/${id}`, data);
  }

  async deleteRole(id: number): Promise<void> {
    return this.delete(`/roles/${id}`);
  }

  // ========== ATTACHMENTS ==========
  async getAttachments(
    params?: PaginationParams
  ): Promise<ApiResponse<Attachment[]>> {
    return this.get<Attachment>("/attachments", params);
  }

  async getAttachment(id: number): Promise<Attachment> {
    const response: AxiosResponse<Attachment> = await this.api.get(
      `/attachments/${id}`
    );
    return response.data;
  }

  async createAttachment(data: CreateAttachmentRequest): Promise<Attachment> {
    return this.post<Attachment>("/attachments", data);
  }

  async updateAttachment(
    id: number,
    data: Partial<CreateAttachmentRequest>
  ): Promise<Attachment> {
    return this.put<Attachment>(`/attachments/${id}`, data);
  }

  async deleteAttachment(id: number): Promise<void> {
    return this.delete(`/attachments/${id}`);
  }

  // ========== IMAGE GALLERY ==========
  async getImageGallery(
    params?: PaginationParams
  ): Promise<ApiResponse<ImageGallery[]>> {
    return this.get<ImageGallery>("/image-gallery", params);
  }

  async getImage(id: number): Promise<ImageGallery> {
    const response: AxiosResponse<ImageGallery> = await this.api.get(
      `/image-gallery/${id}`
    );
    return response.data;
  }

  async createImage(
    data: CreateImageRequest,
    imageFile?: Buffer,
    filename?: string
  ): Promise<ImageGallery> {
    if (imageFile && filename) {
      const formData = new FormData();
      formData.append("type", data.type);
      formData.append("uploaded_to", data.uploaded_to.toString());
      if (data.name) formData.append("name", data.name);
      formData.append("image", imageFile, filename);

      return this.postMultipart<ImageGallery>("/image-gallery", formData);
    } else {
      return this.post<ImageGallery>("/image-gallery", data);
    }
  }

  async updateImage(
    id: number,
    data: { name?: string },
    imageFile?: Buffer,
    filename?: string
  ): Promise<ImageGallery> {
    if (imageFile && filename) {
      const formData = new FormData();
      if (data.name) formData.append("name", data.name);
      formData.append("image", imageFile, filename);

      return this.postMultipart<ImageGallery>(`/image-gallery/${id}`, formData);
    } else {
      return this.put<ImageGallery>(`/image-gallery/${id}`, data);
    }
  }

  async deleteImage(id: number): Promise<void> {
    return this.delete(`/image-gallery/${id}`);
  }

  // ========== SEARCH ==========
  async search(
    query: string,
    params?: { page?: number; count?: number }
  ): Promise<ApiResponse<SearchResult[]>> {
    const searchParams = { query, ...params };
    const response: AxiosResponse<ApiResponse<SearchResult[]>> =
      await this.api.get("/search", { params: searchParams });
    return response.data;
  }

  // ========== CONTENT PERMISSIONS ==========
  async getContentPermissions(
    contentType: string,
    contentId: number
  ): Promise<ContentPermissions> {
    const response: AxiosResponse<ContentPermissions> = await this.api.get(
      `/content-permissions/${contentType}/${contentId}`
    );
    return response.data;
  }

  async updateContentPermissions(
    contentType: string,
    contentId: number,
    permissions: Partial<ContentPermissions>
  ): Promise<ContentPermissions> {
    return this.put<ContentPermissions>(
      `/content-permissions/${contentType}/${contentId}`,
      permissions
    );
  }

  // ========== AUDIT LOG ==========
  async getAuditLog(
    params?: PaginationParams
  ): Promise<ApiResponse<AuditLogEntry[]>> {
    return this.get<AuditLogEntry>("/audit-log", params);
  }

  // ========== RECYCLE BIN ==========
  async getRecycleBin(
    params?: PaginationParams
  ): Promise<ApiResponse<RecycleBinItem[]>> {
    return this.get<RecycleBinItem>("/recycle-bin", params);
  }

  async restoreFromRecycleBin(
    deletionId: number
  ): Promise<{ restore_count: number }> {
    return this.put<{ restore_count: number }>(`/recycle-bin/${deletionId}`);
  }

  async permanentlyDelete(
    deletionId: number
  ): Promise<{ delete_count: number }> {
    const response: AxiosResponse<{ delete_count: number }> =
      await this.api.delete(`/recycle-bin/${deletionId}`);
    return response.data;
  }
}
