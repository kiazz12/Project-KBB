import api from '../api/axios';
import type { ApiResponse, PaginatedResponse } from '../types';

export abstract class BaseService {
  protected abstract basePath: string;

  protected async get<T>(path = '', params?: Record<string, string | number | undefined>): Promise<T> {
    const cleanParams = Object.fromEntries(
      Object.entries(params || {}).filter(([, v]) => v !== undefined && v !== '')
    );
    const res = await api.get<ApiResponse<T>>(`${this.basePath}${path}`, {
      params: Object.keys(cleanParams).length ? cleanParams : undefined,
    });
    return res.data.data;
  }

  protected async post<T>(path = '', data?: unknown): Promise<T> {
    const res = await api.post<ApiResponse<T>>(`${this.basePath}${path}`, data);
    return res.data.data;
  }

  protected async put<T>(path = '', data?: unknown): Promise<T> {
    const res = await api.put<ApiResponse<T>>(`${this.basePath}${path}`, data);
    return res.data.data;
  }

  protected async del(path = ''): Promise<void> {
    await api.delete(`${this.basePath}${path}`);
  }

  protected async getBlob(path = ''): Promise<Blob> {
    const res = await api.get(`${this.basePath}${path}`, { responseType: 'blob' });
    return res.data;
  }

  protected async getPaginated<T>(
    path = '',
    params?: Record<string, string | number | undefined>
  ): Promise<PaginatedResponse<T>> {
    const cleanParams = Object.fromEntries(
      Object.entries(params || {}).filter(([, v]) => v !== undefined && v !== '')
    );
    const res = await api.get<ApiResponse<PaginatedResponse<T>>>(`${this.basePath}${path}`, {
      params: Object.keys(cleanParams).length ? cleanParams : undefined,
    });
    return res.data.data;
  }
}
