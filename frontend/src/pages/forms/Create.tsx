import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { formService } from '../../services';

export default function FormCreate() {
  const navigate = useNavigate();
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await formService.create({ title, description });
      navigate(`/forms/${res.id}/edit`);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal membuat formulir.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 max-w-lg mx-auto animate-fade-in-up">
      <div className="flex items-center gap-3">
        <button onClick={() => navigate('/forms')} className="p-2 rounded-xl text-white/40 hover:text-white/70 hover:bg-white/5 transition">
          <ArrowLeftIcon className="h-5 w-5" />
        </button>
        <div>
          <h1 className="text-xl font-bold text-white">Buat Formulir Baru</h1>
          <p className="text-white/40 text-sm mt-0.5">Mulai dengan judul dan deskripsi</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 space-y-5">
        {error && (
          <div className="flex items-center gap-2.5 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
            <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {error}
          </div>
        )}
        <div>
          <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Judul Formulir</label>
          <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} placeholder="Judul formulir" required
            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <div>
          <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Deskripsi (opsional)</label>
          <textarea value={description} onChange={(e) => setDescription(e.target.value)} rows={3} placeholder="Deskripsi singkat"
            className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition resize-none" />
        </div>
        <div className="flex justify-end gap-3 pt-2">
          <button type="button" onClick={() => navigate('/forms')} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 transition">Batal</button>
          <button type="submit" disabled={loading} className="px-5 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition shadow-lg shadow-kbb-700/30">
            {loading ? 'Menyimpan...' : 'Buat & Lanjutkan'}
          </button>
        </div>
      </form>
    </div>
  );
}
