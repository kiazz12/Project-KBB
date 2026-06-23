import { createContext, useContext, useState, useEffect, useCallback, type ReactNode } from 'react';
import { authService } from '../services';
import type { User } from '../types';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  isSuperAdmin: boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(() => {
    const stored = localStorage.getItem('user');
    return stored ? JSON.parse(stored) : null;
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      setLoading(false);
      return;
    }
    authService
      .me()
      .then((u) => {
        setUser(u);
        localStorage.setItem('user', JSON.stringify(u));
      })
      .catch(() => {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        setUser(null);
      })
      .finally(() => setLoading(false));
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const result = await authService.login(email, password);
    localStorage.setItem('auth_token', result.token);
    localStorage.setItem('user', JSON.stringify(result.user));
    setUser(result.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await authService.logout();
    } catch {
    }
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    setUser(null);
  }, []);

  const isSuperAdmin = user?.role === 'super_admin';

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, isSuperAdmin }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
