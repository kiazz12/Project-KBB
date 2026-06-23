import { BaseService } from './BaseService';
import type {
  Form, FormField, FormSubmission, AnalyticsData,
  PaginatedResponse, User, DashboardStats,
} from '../types';

export class FormService extends BaseService {
  protected basePath = '/forms';

  list(params?: Record<string, string | number | undefined>): Promise<PaginatedResponse<Form>> {
    return this.getPaginated<Form>('', params);
  }

  show(id: number): Promise<Form> {
    return this.get<Form>(`/${id}`);
  }

  create(data: { title: string; description?: string; settings?: Record<string, unknown> }): Promise<Form> {
    return this.post<Form>('', data);
  }

  update(id: number, data: Partial<Form>): Promise<Form> {
    return this.put<Form>(`/${id}`, data);
  }

  delete(id: number): Promise<void> {
    return this.del(`/${id}`);
  }

  duplicate(id: number): Promise<Form> {
    return this.post<Form>(`/${id}/duplicate`);
  }

  publish(id: number): Promise<Form> {
    return this.post<Form>(`/${id}/publish`);
  }

  close(id: number): Promise<Form> {
    return this.post<Form>(`/${id}/close`);
  }

  getAnalytics(id: number): Promise<AnalyticsData> {
    return this.get<AnalyticsData>(`/${id}/analytics`);
  }

  exportCsv(id: number): Promise<Blob> {
    return this.getBlob(`/${id}/export/csv`);
  }

  addField(formId: number, data: Partial<FormField>): Promise<FormField> {
    return this.post<FormField>(`/${formId}/fields`, data);
  }

  updateField(formId: number, fieldId: number, data: Partial<FormField>): Promise<FormField> {
    return this.put<FormField>(`/${formId}/fields/${fieldId}`, data);
  }

  deleteField(formId: number, fieldId: number): Promise<void> {
    return this.del(`/${formId}/fields/${fieldId}`);
  }

  reorderFields(formId: number, fieldIds: number[]): Promise<void> {
    return this.post(`/${formId}/fields/reorder`, { field_ids: fieldIds });
  }

  getSubmissions(
    formId: number,
    params?: Record<string, string | number | undefined>
  ): Promise<PaginatedResponse<FormSubmission>> {
    return this.getPaginated<FormSubmission>(`/${formId}/submissions`, params);
  }

  getSubmission(formId: number, submissionId: number): Promise<FormSubmission> {
    return this.get<FormSubmission>(`/${formId}/submissions/${submissionId}`);
  }

  deleteSubmission(formId: number, submissionId: number): Promise<void> {
    return this.del(`/${formId}/submissions/${submissionId}`);
  }

  getPublicForm(slug: string): Promise<Form & { fields: FormField[] }> {
    return this.get<Form & { fields: FormField[] }>(`/public/${slug}`);
  }

  submitPublicForm(slug: string, data: Record<string, unknown>): Promise<FormSubmission> {
    return this.post<FormSubmission>(`/public/${slug}`, data);
  }

  getStats(): Promise<DashboardStats> {
    return this.get<DashboardStats>('/dashboard/stats');
  }

  getRecentForms(): Promise<Form[]> {
    return this.get<Form[]>('/dashboard/recent-forms');
  }

  getUsers(): Promise<User[]> {
    return this.get<User[]>('/users');
  }

  createUser(data: { name: string; email: string; password: string; role: string; nip?: string; opd?: string }): Promise<User> {
    return this.post<User>('/users', data);
  }

  updateUser(id: number, data: Partial<User>): Promise<User> {
    return this.put<User>(`/users/${id}`, data);
  }

  deleteUser(id: number): Promise<void> {
    return this.del(`/users/${id}`);
  }
}
