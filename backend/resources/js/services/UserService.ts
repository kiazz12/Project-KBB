import { BaseService } from './BaseService';
import type { User, PaginatedResponse } from '../types';

export class UserService extends BaseService {
  protected basePath = '/users';

  getUsers(params?: Record<string, string | number | undefined>): Promise<User[]> {
    return this.get<User[]>('', params);
  }

  createUser(data: {
    name: string;
    email: string;
    password: string;
    role: User['role'];
    nip?: string;
    opd?: string;
  }): Promise<User> {
    return this.post<User>('', data);
  }

  updateUser(id: number, data: Partial<User & { password?: string }>): Promise<User> {
    return this.put<User>(`/${id}`, data);
  }

  deleteUser(id: number): Promise<void> {
    return this.del(`/${id}`);
  }

  getUserForms(id: number, params?: Record<string, string | number | undefined>): Promise<any> {
    return this.get<any>(`/${id}/forms`, params);
  }
}
