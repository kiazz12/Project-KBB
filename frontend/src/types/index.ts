export interface User {
  id: number;
  name: string;
  email: string;
  role: 'super_admin' | 'admin' | 'operator' | 'viewer';
  nip?: string;
  opd?: string;
  created_at?: string;
}

export interface FormSettings {
  confirmation_type: 'message' | 'url';
  confirmation_message: string;
  confirmation_url?: string;
  show_progress_bar: boolean;
  shuffle_fields: boolean;
  limit_one_response: boolean;
}

export interface Form {
  id: number;
  uuid: string;
  slug: string;
  title: string;
  description?: string;
  user_id: number;
  status: 'draft' | 'published' | 'closed';
  settings?: Record<string, unknown>;
  starts_at?: string;
  ends_at?: string;
  max_submissions?: number;
  require_auth: boolean;
  collect_ip: boolean;
  show_kbb_logo: boolean;
  confirmation_message?: string;
  limit_one_response: boolean;
  confirmation_type?: string;
  created_at: string;
  updated_at?: string;
  fields_count?: number;
  submissions_count?: number;
  user?: User;
}

export interface FormField {
  id: number;
  form_id: number;
  type: 'text' | 'textarea' | 'email' | 'number' | 'select' | 'radio' | 'checkbox' | 'date' | 'time' | 'file' | 'signature' | 'heading' | 'paragraph';
  label: string;
  placeholder?: string;
  help_text?: string;
  required: boolean;
  options?: string[];
  order: number;
  min_length?: number;
  max_length?: number;
  default_value?: string;
}

export interface FormSubmission {
  id: number;
  uuid: string;
  form_id: number;
  user_id?: number;
  ip_address?: string;
  user_agent?: string;
  submitted_at: string;
  created_at: string;
  data?: SubmissionData[];
}

export interface SubmissionData {
  id: number;
  submission_id: number;
  form_field_id: number;
  value: string;
  field?: FormField;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface PaginatedResponse<T> {
  current_page: number;
  data: T[];
  first_page_url?: string;
  from?: number;
  last_page: number;
  last_page_url?: string;
  next_page_url?: string | null;
  path: string;
  per_page: number;
  prev_page_url?: string | null;
  to?: number;
  total: number;
}

export interface DashboardStats {
  total_forms: number;
  published_forms: number;
  submissions_today: number;
  total_submissions: number;
}

export interface AnalyticsData {
  total_submissions: number;
  submissions_by_date: { date: string; count: number }[];
  field_analytics: {
    field_id: number;
    field_label: string;
    field_type: string;
    counts: Record<string, number>;
  }[];
}
