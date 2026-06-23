import { Outlet } from 'react-router-dom';

export default function AuthLayout() {
  return (
    <div className="min-h-screen flex items-center justify-center p-4 relative overflow-hidden bg-[#0a0a1a]">
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute -top-40 -left-40 w-[30rem] h-[30rem] bg-kbb-700/20 rounded-full blur-[120px]" />
        <div className="absolute -bottom-40 -right-40 w-[25rem] h-[25rem] bg-gold-400/10 rounded-full blur-[100px]" />
      </div>
      <div className="w-full max-w-md relative z-10">
        <div className="text-center mb-8 animate-fade-in-up">
          <div className="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-kbb-700 to-kbb-950 shadow-2xl shadow-kbb-700/30 ring-1 ring-white/10 flex items-center justify-center">
            <img src="/images/kbb-logo.png" alt="KBB" className="w-12 h-12" />
          </div>
          <h1 className="text-3xl font-bold text-white tracking-tight">KBB Forms</h1>
          <p className="text-white/50 text-sm mt-1">Sistem Formulir Elektronik</p>
          <span className="inline-block mt-2 px-3 py-0.5 rounded-full bg-gold-400/10 border border-gold-400/20 text-gold-400/80 text-xs font-medium">
            Kabupaten Bandung Barat
          </span>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl shadow-black/40 animate-fade-in-up">
          <Outlet />
        </div>
        <p className="text-white/20 text-xs text-center mt-8">
          &copy; 2026 Pemerintah Kabupaten Bandung Barat
        </p>
      </div>
    </div>
  );
}
