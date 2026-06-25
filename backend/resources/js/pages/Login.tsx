import { useState, FormEvent, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { authService } from '../services';

export default function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    const handler = (e: MediaQueryListEvent) => document.documentElement.classList.toggle('dark', e.matches);
    document.documentElement.classList.toggle('dark', mq.matches);
    mq.addEventListener('change', handler);
    return () => mq.removeEventListener('change', handler);
  }, []);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const result = await authService.login(email, password);
      localStorage.setItem('auth_token', result.token);
      localStorage.setItem('user', JSON.stringify(result.user));
      router.visit('/dashboard');
    } catch (err: any) {
      if (err.response?.status === 429) {
        setError('Terlalu banyak percobaan. Silakan coba lagi dalam 1 menit.');
      } else {
        setError(err.response?.data?.message || 'Email atau password salah.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex relative overflow-hidden bg-white dark:bg-gray-950">
      {/* Animated gradient background */}
      <div className="absolute inset-0 bg-gradient-to-br from-blue-50/80 via-white to-indigo-50/60 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950" />

      {/* Floating decorative orbs */}
      <div className="absolute -top-48 -right-48 w-96 h-96 rounded-full bg-gradient-to-br from-blue-200/40 to-indigo-300/30 dark:from-blue-500/5 dark:to-indigo-500/5 blur-3xl animate-pulse-soft" />
      <div className="absolute -bottom-48 -left-48 w-96 h-96 rounded-full bg-gradient-to-tr from-amber-200/30 to-orange-200/20 dark:from-amber-500/5 dark:to-orange-500/5 blur-3xl animate-pulse-soft" style={{ animationDelay: '2s' }} />
      <div className="absolute top-1/3 -right-24 w-64 h-64 rounded-full bg-gradient-to-bl from-sky-200/20 to-blue-100/10 dark:from-sky-500/5 dark:to-blue-500/3 blur-3xl" />

      {/* Dot grid pattern overlay */}
      <div className="absolute inset-0 opacity-[0.03] dark:opacity-[0.07]" style={{
        backgroundImage: `radial-gradient(circle at 1px 1px, #003778 1px, transparent 0)`,
        backgroundSize: '32px 32px',
      }} />

      {/* Subtle wave at bottom */}
      <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[#003778]/5 dark:from-blue-500/10 to-transparent" />

      {/* Accent bar */}
      <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#003778] via-[#C8A45C] to-[#003778]" />

      <div className="min-h-screen w-full flex items-center justify-center p-4 sm:p-6 relative z-10">
        <div className="w-full max-w-[420px]">

          {/* Brand Header */}
          <div className="text-center mb-10">
            <div className="relative inline-flex mb-6 group">
              <div className="w-22 h-22 sm:w-24 sm:h-24 rounded-2xl bg-white dark:bg-gray-800 shadow-lg dark:shadow-2xl dark:shadow-black/30 flex items-center justify-center p-4 sm:p-5 ring-1 ring-gray-100 dark:ring-gray-700 transition-all duration-500 group-hover:shadow-xl group-hover:-translate-y-0.5">
                <img src="/images/kbb-logo.png" alt="KBB" className="w-full h-full object-contain" />
              </div>
              <div className="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-[3px] border-white dark:border-gray-800 flex items-center justify-center shadow-lg">
                <svg className="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                </svg>
              </div>
            </div>
            <h1 className="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              Sistem Formulir Digital
            </h1>
            <div className="flex items-center justify-center gap-2 mt-2">
              <span className="w-6 h-px bg-[#C8A45C]/50 dark:bg-[#C8A45C]/30" />
              <p className="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-wide">
                PEMERINTAH KABUPATEN BANDUNG BARAT
              </p>
              <span className="w-6 h-px bg-[#C8A45C]/50 dark:bg-[#C8A45C]/30" />
            </div>
          </div>

          {/* Login Card */}
          <div className="relative">
            {/* Glow effect behind card */}
            <div className="absolute -inset-1 bg-gradient-to-r from-[#003778]/10 via-[#C8A45C]/10 to-[#003778]/10 dark:from-blue-500/10 dark:via-amber-500/10 dark:to-blue-500/10 rounded-3xl blur-xl opacity-60" />

            <div className="relative bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-xl dark:shadow-2xl dark:shadow-black/30 border border-gray-200/60 dark:border-gray-700/60 p-8 sm:p-10 transition-all duration-500 hover:shadow-2xl dark:hover:shadow-black/40">
              {/* Subtle inner gradient */}
              <div className="absolute top-0 left-0 right-0 h-24 bg-gradient-to-b from-blue-50/40 to-transparent dark:from-blue-500/5 rounded-t-2xl pointer-events-none" />

              <div className="relative">
                <div className="mb-7">
                  <h2 className="text-xl font-semibold text-gray-900 dark:text-white">Selamat Datang</h2>
                  <p className="text-sm text-gray-500 dark:text-gray-400 mt-1.5 leading-relaxed">
                    Silakan masuk menggunakan akun yang telah diberikan oleh admin
                  </p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-5">
                  {error && (
                    <div role="alert" className="flex items-start gap-3 px-4 py-3 bg-red-50/80 dark:bg-red-900/30 backdrop-blur border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm animate-shake shadow-sm">
                      <svg className="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span className="flex-1">{error}</span>
                    </div>
                  )}

                  <div className="space-y-1.5">
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <div className="relative group">
                      <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 group-focus-within:text-[#003778] dark:group-focus-within:text-blue-400 transition-colors duration-200">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                      </div>
                      <input
                        type="email" value={email} onChange={(e) => setEmail(e.target.value)}
                        placeholder="admin@dinas.com" required autoFocus
                        className="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-[#003778] dark:focus:border-blue-500 focus:ring-2 focus:ring-[#003778]/20 dark:focus:ring-blue-500/30 transition-all duration-200"
                      />
                    </div>
                  </div>

                  <div className="space-y-1.5">
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <div className="relative group">
                      <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500 group-focus-within:text-[#003778] dark:group-focus-within:text-blue-400 transition-colors duration-200">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                      </div>
                      <input
                        type={showPassword ? 'text' : 'password'} value={password} onChange={(e) => setPassword(e.target.value)}
                        placeholder="••••••••" required
                        className="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-[#003778] dark:focus:border-blue-500 focus:ring-2 focus:ring-[#003778]/20 dark:focus:ring-blue-500/30 transition-all duration-200"
                      />
                      <button type="button" onClick={() => setShowPassword(!showPassword)}
                        aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                        className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
                        {showPassword ? (
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                        ) : (
                          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        )}
                      </button>
                    </div>
                  </div>

                  <button type="submit" disabled={loading}
                    className="relative w-full py-2.5 rounded-xl bg-gradient-to-r from-[#003778] via-[#002a5c] to-[#003778] hover:from-[#002a5c] hover:via-[#001e42] hover:to-[#002a5c] text-white font-semibold text-sm shadow-lg shadow-[#003778]/25 dark:shadow-blue-900/40 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed active:scale-[0.98] overflow-hidden group">
                    {/* Shine effect on hover */}
                    <span className="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700" />
                    {loading ? (
                      <>
                        <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Memproses...
                      </>
                    ) : (
                      <>
                        Masuk
                        <svg className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                      </>
                    )}
                  </button>
                </form>

                <div className="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/50">
                  <div className="flex flex-col items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                    <div className="flex items-center gap-3">
                      <img src="/images/kbb-logo.png" alt="KBB" className="w-5 h-5 opacity-50 dark:opacity-40" />
                      <span className="w-px h-3 bg-gray-200 dark:bg-gray-700" />
                      <span>&copy; {new Date().getFullYear()} Pemkab Bandung Barat</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Version tag */}
          <div className="text-center mt-6">
            <span className="text-[11px] text-gray-300 dark:text-gray-600 tracking-wider uppercase">Sistem Formulir Digital v2.0</span>
          </div>
        </div>
      </div>
    </div>
  );
}