import { useState } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import {
  Squares2X2Icon,
  DocumentTextIcon,
  UsersIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  KeyIcon,
} from '@heroicons/react/24/outline';
import { useAuth } from '../context/AuthContext';

const navItems = [
  { label: 'Dashboard', path: '/dashboard', icon: Squares2X2Icon },
  { label: 'Forms', path: '/forms', icon: DocumentTextIcon },
];

export default function AppLayout() {
  const { user, logout, isSuperAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const isActive = (path: string) => {
    if (path === '/dashboard') return location.pathname === '/dashboard';
    return location.pathname.startsWith(path);
  };

  return (
    <div className="min-h-screen flex bg-[#0a0a1a]">
      {sidebarOpen && (
        <div className="fixed inset-0 bg-black/60 z-40 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}

      <aside className={`fixed lg:sticky top-0 left-0 z-50 h-screen w-64 flex flex-col transition-transform duration-300 ${sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}`}
        style={{ background: 'rgba(10, 10, 26, 0.98)' }}
      >
        <div className="flex items-center gap-3 px-5 py-5 border-b border-white/5">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-kbb-700 to-kbb-950 shadow-lg shadow-kbb-700/30 ring-1 ring-white/10 flex items-center justify-center flex-shrink-0">
            <img src="/images/kbb-logo.png" alt="KBB" className="w-6 h-6" />
          </div>
          <div className="flex-1 min-w-0">
            <h2 className="text-white font-semibold text-sm leading-tight">KBB Forms</h2>
            <p className="text-white/30 text-xs">Kab. Bandung Barat</p>
          </div>
          <button onClick={() => setSidebarOpen(false)} className="lg:hidden text-white/40 hover:text-white">
            <XMarkIcon className="h-5 w-5" />
          </button>
        </div>

        <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
          {navItems.map((item) => {
            const Icon = item.icon;
            return (
              <button
                key={item.path}
                onClick={() => { navigate(item.path); setSidebarOpen(false); }}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${
                  isActive(item.path)
                    ? 'bg-kbb-700/30 text-white font-medium border border-gold-400/15'
                    : 'text-white/40 hover:text-white/70 hover:bg-white/5 border border-transparent'
                }`}
              >
                <Icon className={`h-5 w-5 flex-shrink-0 ${isActive(item.path) ? 'text-gold-400' : ''}`} />
                <span>{item.label}</span>
              </button>
            );
          })}

          {isSuperAdmin && (
            <button
              onClick={() => { navigate('/users'); setSidebarOpen(false); }}
              className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${
                isActive('/users')
                  ? 'bg-kbb-700/30 text-white font-medium border border-gold-400/15'
                  : 'text-white/40 hover:text-white/70 hover:bg-white/5 border border-transparent'
              }`}
            >
              <UsersIcon className={`h-5 w-5 flex-shrink-0 ${isActive('/users') ? 'text-gold-400' : ''}`} />
              <span>Users</span>
            </button>
          )}
        </nav>

        <div className="border-t border-white/5 px-4 py-4">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-9 h-9 rounded-full bg-gradient-to-br from-gold-400 to-gold-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0 shadow-lg shadow-gold-400/25">
              {user?.name?.charAt(0)?.toUpperCase()}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-white text-sm font-medium truncate">{user?.name}</p>
              <p className="text-white/30 text-xs truncate capitalize">{user?.role?.replace('_', ' ')}</p>
            </div>
          </div>
          <button onClick={() => navigate('/change-password')} className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-white/40 hover:text-white/70 hover:bg-white/5 transition mb-1">
            <KeyIcon className="h-4 w-4" />
            <span>Ubah Password</span>
          </button>
          <button onClick={handleLogout} className="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-red-400/50 hover:text-red-300 hover:bg-red-500/5 transition">
            <ArrowRightOnRectangleIcon className="h-4 w-4" />
            <span>Logout</span>
          </button>
        </div>
      </aside>

      <div className="flex-1 flex flex-col min-h-screen min-w-0">
        <header className="lg:hidden flex items-center justify-between px-4 py-3 bg-[#0a0a1a]/90 border-b border-white/5">
          <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-kbb-700 to-kbb-950 flex items-center justify-center ring-1 ring-white/10">
            <img src="/images/kbb-logo.png" alt="KBB" className="w-5 h-5" />
          </div>
          <span className="text-white font-semibold text-sm">KBB Forms</span>
          <button onClick={() => setSidebarOpen(true)} className="text-white/40 p-1.5 rounded-lg hover:bg-white/10 transition">
            <Bars3Icon className="h-5 w-5" />
          </button>
        </header>
        <main className="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
