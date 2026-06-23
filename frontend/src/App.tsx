import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import AuthLayout from './layouts/AuthLayout';
import AppLayout from './layouts/AppLayout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import FormsIndex from './pages/forms/Index';
import FormCreate from './pages/forms/Create';
import FormEdit from './pages/forms/Edit';
import FormShow from './pages/forms/Show';
import FormAnalytics from './pages/forms/Analytics';
import SubmissionsIndex from './pages/forms/submissions/Index';
import SubmissionShow from './pages/forms/submissions/Show';
import UsersIndex from './pages/UsersIndex';
import ChangePassword from './pages/ChangePassword';
import PublicForm from './pages/PublicForm';

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<AuthLayout />}>
            <Route index element={<Login />} />
          </Route>

          <Route path="/form/:slug" element={<PublicForm />} />

          <Route
            element={
              <ProtectedRoute>
                <AppLayout />
              </ProtectedRoute>
            }
          >
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/forms" element={<FormsIndex />} />
            <Route path="/forms/create" element={<FormCreate />} />
            <Route path="/forms/:id/edit" element={<FormEdit />} />
            <Route path="/forms/:id" element={<FormShow />} />
            <Route path="/forms/:id/analytics" element={<FormAnalytics />} />
            <Route path="/forms/:id/submissions" element={<SubmissionsIndex />} />
            <Route path="/forms/:formId/submissions/:id" element={<SubmissionShow />} />
            <Route path="/users" element={<UsersIndex />} />
            <Route path="/change-password" element={<ChangePassword />} />
          </Route>

          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
