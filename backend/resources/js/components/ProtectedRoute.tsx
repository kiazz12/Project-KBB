import { usePage } from '@inertiajs/react';
import LoadingSpinner from './LoadingSpinner';

export default function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { auth } = usePage().props as unknown as { auth: { user: unknown } };

  if (auth.user === undefined) return <LoadingSpinner />;
  if (!auth.user) {
    window.location.href = '/login';
    return null;
  }

  return <>{children}</>;
}
