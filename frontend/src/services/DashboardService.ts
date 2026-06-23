import { BaseService } from './BaseService';
import type { DashboardStats, Form } from '../types';

export class DashboardService extends BaseService {
  protected basePath = '/dashboard';

  getStats(): Promise<DashboardStats> {
    return this.get<DashboardStats>('/stats');
  }

  getRecentForms(): Promise<Form[]> {
    return this.get<Form[]>('/recent-forms');
  }
}
