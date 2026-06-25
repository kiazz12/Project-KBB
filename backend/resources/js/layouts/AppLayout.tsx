import { Link, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import axios from 'axios';

function SidebarIcon({ collapsed, children }: { collapsed: boolean; children: React.ReactNode }) {
  return (
    <span className="shrink-0 flex items-center justify-center w-5 h-5">{children}</span>
  );
}

function SidebarLabel({ collapsed, children }: { collapsed: boolean; children: React.ReactNode }) {
  return (
    <span className={`text-sm truncate transition-all duration-200 ${
      collapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 w-auto'
    }`}>
      {children}
    </span>
  );
}

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const { auth } = usePage().props as unknown as { auth: { user: { name: string; email: string; role: string } | null } };
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(() => localStorage.getItem('sidebar_collapsed') === 'true');
  const user = auth.user;

  useEffect(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const handler = (e: MediaQueryListEvent) => document.documentElement.classList.toggle('dark', e.matches);
    document.documentElement.classList.toggle('dark', mq.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  useEffect(() => {
    localStorage.setItem('sidebar_collapsed', String(collapsed));
  }, [collapsed]);

  const isActive = (path: string) => window.location.pathname === path || (path !== '/' && window.location.pathname.startsWith(path));

  const navItems = [
    {
      path: '/dashboard',
      label: 'Dashboard',
      icon: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>,
    },
    {
      path: '/forms',
      label: 'Formulir',
      icon: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>,
    },
    ...(user?.role === 'super_admin'
      ? [{
          path: '/users',
          label: 'Pengguna',
          icon: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" /></svg>,
        }]
      : []),
    {
      path: '/change-password',
      label: 'Ganti Password',
      icon: <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>,
    },
  ];

  const handleLogout = async () => {
    try { await axios.post('/logout'); } catch { /* ignore */ }
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.visit('/login');
  };

  return (
    <div className="min-h-screen flex bg-gray-50 dark:bg-gray-900">
      <a href="#main-content" className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-gray-900 focus:rounded-lg focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#003778]">
        Langsung ke konten utama
      </a>
      {sidebarOpen && (
        <div className="fixed inset-0 z-30 bg-black/40 lg:hidden" onClick={() => setSidebarOpen(false)} role="presentation" />
      )}

      <aside className={`fixed inset-y-0 left-0 z-40 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto ${
        collapsed ? 'w-16' : 'w-60'
      } ${
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      }`}>
        <div className="flex items-center h-16 border-b border-gray-200 dark:border-gray-700 shrink-0 px-3 gap-2">
          <div className={`flex items-center flex-1 min-w-0 gap-3 ${collapsed ? 'justify-center' : ''}`}>
            <img src="/images/kbb-logo.png" alt="KBB" className="w-8 h-8 shrink-0" />
            <div className={`min-w-0 transition-all duration-300 ${
              collapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 w-auto'
            }`}>
              <h1 className="text-sm font-bold dark:text-white leading-tight truncate">Pemkab Bandung Barat</h1>
              <p className="text-[11px] text-gray-400 truncate">Sistem Formulir Digital</p>
            </div>
          </div>
          <button onClick={() => setCollapsed(!collapsed)} className="hidden lg:inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors shrink-0" title={collapsed ? 'Perluas' : 'Persempit'} aria-label={collapsed ? 'Perluas sidebar' : 'Persempit sidebar'}>
            <svg className={`w-4 h-4 transition-transform duration-300 ${collapsed ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
          </button>
        </div>

        <nav className="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto" aria-label="Navigasi utama">
          {navItems.map((item) => (
            <Link key={item.path} href={item.path}
              className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 ${
                isActive(item.path)
                  ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700/50'
              } ${collapsed ? 'justify-center px-2' : ''}`}
              title={collapsed ? item.label : undefined}>
              <SidebarIcon collapsed={false}>{item.icon}</SidebarIcon>
              <SidebarLabel collapsed={collapsed}>{item.label}</SidebarLabel>
            </Link>
          ))}
        </nav>

        <div className={`border-t border-gray-200 dark:border-gray-700 shrink-0 transition-all duration-300 ${collapsed ? 'p-2' : 'p-4'}`}>
          <div className={`flex ${collapsed ? 'flex-col items-center gap-2' : 'items-center gap-3 mb-3'}`}>
            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-sm font-semibold shrink-0 shadow-sm">
              {user?.name?.charAt(0)?.toUpperCase() || '?'}
            </div>
            <div className={`min-w-0 flex-1 transition-all duration-300 ${
              collapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 w-auto'
            }`}>
              <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{user?.name || 'User'}</p>
              <p className="text-xs text-gray-400 truncate">{user?.email || ''}</p>
            </div>
          </div>
          <button onClick={handleLogout}
            className={`flex items-center gap-2 rounded-lg text-sm text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200 ${
              collapsed
                ? 'justify-center w-9 h-9 mx-auto p-2'
                : 'w-full px-3 py-2'
            }`} title={collapsed ? 'Keluar' : undefined} aria-label="Keluar dari aplikasi">
            <svg className="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            <span className={`transition-all duration-300 ${
              collapsed ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 w-auto'
            }`}>Keluar</span>
          </button>
        </div>
      </aside>

      <div className="flex-1 flex flex-col min-w-0">
        <header className="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 h-14 flex items-center gap-3 lg:hidden shadow-sm">
          <button onClick={() => setSidebarOpen(true)} className="p-2 -ml-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" aria-label="Buka menu navigasi" aria-expanded={sidebarOpen}>
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
          </button>
          <img src="/images/kbb-logo.png" alt="KBB" className="w-6 h-6" />
          <span className="text-sm font-semibold text-gray-900 dark:text-white flex-1">Pemkab Bandung Barat</span>
        </header>
        <main id="main-content" className="flex-1 p-4 lg:p-6 overflow-x-hidden">
          {children}
        </main>
      </div>
    </div>
  );
}
