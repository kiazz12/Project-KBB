import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { formService } from '../../services';

export default function FormCreate() {
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
      router.visit(`/forms/${res.id}/edit`);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal membuat formulir.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6 max-w-lg mx-auto">
      <div className="flex items-center gap-3 animate-fade-in-up">
        <Link href="/forms" className="p-2 rounded-xl text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Kembali ke daftar formulir">
          <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </Link>
        <div>
          <h1 className="text-xl font-bold text-gray-900 dark:text-white">Buat Formulir Baru</h1>
          <p className="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Mulai dengan judul dan deskripsi</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="kbb-card p-6 space-y-5 animate-fade-in-up">
        {error && (
          <div role="alert" className="flex items-center gap-2.5 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
            <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {error}
          </div>
        )}
        <div>
          <label className="kbb-label">Judul Formulir</label>
          <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} placeholder="Judul formulir" required className="kbb-input" />
        </div>
        <div>
          <label className="kbb-label">Deskripsi (opsional)</label>
          <textarea value={description} onChange={(e) => setDescription(e.target.value)} rows={3} placeholder="Deskripsi singkat" className="kbb-input resize-none" />
        </div>
        <div className="flex justify-end gap-3 pt-2">
          <Link href="/forms" className="kbb-btn kbb-btn-ghost">Batal</Link>
          <button type="submit" disabled={loading} className="kbb-btn kbb-btn-primary">{loading ? 'Menyimpan...' : 'Buat & Lanjutkan'}</button>
        </div>
      </form>
    </div>
  );
}
