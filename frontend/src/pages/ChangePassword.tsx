import { useState } from 'react';
import { KeyIcon, CheckIcon } from '@heroicons/react/24/outline';
import { authService } from '../services';

export default function ChangePassword() {
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [newPasswordConfirmation, setNewPasswordConfirmation] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    if (newPassword !== newPasswordConfirmation) {
      setError('Konfirmasi password tidak cocok.');
      return;
    }
    setLoading(true);
    try {
      await authService.changePassword({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirmation,
      });
      setSuccess('Password berhasil diubah.');
      setCurrentPassword(''); setNewPassword(''); setNewPasswordConfirmation('');
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal mengubah password.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 max-w-lg mx-auto animate-fade-in-up">
      <div className="flex items-center gap-3">
        <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
          <KeyIcon className="h-6 w-6 text-gold-400" />
        </div>
        <div>
          <h1 className="text-xl font-bold text-white">Ubah Password</h1>
          <p className="text-white/40 text-sm mt-0.5">Perbarui password akun Anda</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 space-y-5">
        {error && (
          <div className="flex items-center gap-2.5 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
            <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {error}
          </div>
        )}
        {success && (
          <div className="flex items-center gap-2.5 px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-400 text-sm">
            <CheckIcon className="w-4 h-4 flex-shrink-0" /> {success}
          </div>
        )}
        <div>
          <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Password Saat Ini</label>
          <input type="password" value={currentPassword} onChange={(e) => setCurrentPassword(e.target.value)} required
            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <div>
          <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Password Baru</label>
          <input type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} required minLength={8}
            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <div>
          <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Konfirmasi Password Baru</label>
          <input type="password" value={newPasswordConfirmation} onChange={(e) => setNewPasswordConfirmation(e.target.value)} required minLength={8}
            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <button type="submit" disabled={loading}
          className="w-full py-2.5 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white font-semibold text-sm transition shadow-lg shadow-kbb-700/30 flex items-center justify-center gap-2">
          {loading && <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>}
          {loading ? 'Menyimpan...' : 'Ubah Password'}
        </button>
      </form>
    </div>
  );
}
