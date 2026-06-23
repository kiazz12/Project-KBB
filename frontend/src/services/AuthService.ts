import { BaseService } from './BaseService';
import type { User } from '../types';

interface LoginResponse {
  user: User;
  token: string;
}

export class AuthService extends BaseService {
  protected basePath = '/auth';

  async login(email: string, password: string): Promise<LoginResponse> {
    return this.post<LoginResponse>('/login', { email, password });
  }

  async logout(): Promise<void> {
    await this.post('/logout');
  }

  async me(): Promise<User> {
    return this.get<User>('/me');
  }

  async changePassword(data: {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
  }): Promise<void> {
    await this.post('/change-password', data);
  }
}
