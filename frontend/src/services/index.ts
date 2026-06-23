import { AuthService } from './AuthService';
import { FormService } from './FormService';
import { DashboardService } from './DashboardService';
import { UserService } from './UserService';

export const authService = new AuthService();
export const formService = new FormService();
export const dashboardService = new DashboardService();
export const userService = new UserService();
