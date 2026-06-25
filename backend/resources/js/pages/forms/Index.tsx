import { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { formService } from '../../services';
import LoadingSpinner from '../../components/LoadingSpinner';
import ConfirmDialog from '../../components/ConfirmDialog';

const FILTERS = [
  { value: '', label: 'Semua' },
  { value: 'draft', label: 'Draft' },
  { value: 'published', label: 'Published' },
  { value: 'closed', label: 'Closed' },
];

const STATUS_STYLES: Record<string, string> = {
  draft: 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
  published: 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800',
  closed: 'bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-600',
};

export default function FormsIndex() {
  const { auth } = usePage().props as unknown as { auth: { user: { role: string } | null } };
  const isSuperAdmin = auth.user?.role === 'super_admin';
  const [forms, setForms] = useState<any[]>([]);
  const [meta, setMeta] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const fetchForms = async () => {
    setLoading(true);
    try {
      const res = await formService.list({ search: search || undefined, status: status || undefined, page, per_page: 15 });
      setForms(res.data);
      setMeta({ current_page: res.current_page, last_page: res.last_page, total: res.total });
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchForms(); }, [page, status, search]);

  const handleSearch = (e: React.FormEvent) => { e.preventDefault(); setPage(1); };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await formService.delete(deleteId);
      setForms((prev) => prev.filter((f: any) => f.id !== deleteId));
      setDeleteId(null);
    } catch { /* ignore */ }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="animate-fade-in-up">
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Formulir</h1>
          <p className="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Kelola formulir {meta ? `(${meta.total})` : ''}</p>
        </div>
        <Link href="/forms/create" className="kbb-btn kbb-btn-primary animate-fade-in-up">
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
          </svg>
          Form Baru
        </Link>
      </div>

      <div className="flex flex-wrap gap-3 items-center">
        <form onSubmit={handleSearch} className="flex-1 min-w-[200px] max-w-sm">
          <div className="relative">
            <svg className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text" value={search} onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari form..."
              className="w-full pl-9 pr-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:border-[#003778] dark:focus:border-blue-500 focus:ring-2 focus:ring-[#003778]/10 dark:focus:ring-blue-500/20 transition shadow-sm"
            />
          </div>
        </form>
        <div className="flex gap-1.5">
          {FILTERS.map((f) => (
            <button
              key={f.value}
              onClick={() => { setStatus(f.value); setPage(1); }}
              className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 ${
                status === f.value
                  ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
              }`}
            >
              {f.label}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <LoadingSpinner />
      ) : forms.length === 0 ? (
        <div className="kbb-card p-12 text-center animate-fade-in-up">
          <div className="w-14 h-14 mx-auto mb-4 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center">
            <svg className="h-7 w-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p className="text-gray-500 dark:text-gray-400 text-sm">Tidak ada form ditemukan.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {forms.map((form: any, i: number) => (
            <div key={form.id} className="kbb-card group animate-fade-in-up" style={{ animationDelay: `${i * 40}ms`, animationFillMode: 'both' }}>
              <div className="p-5">
                <div className="flex items-start justify-between mb-3">
                  <div className="flex items-center gap-3 min-w-0">
                    <div className="w-10 h-10 rounded-lg bg-[#C8A45C]/10 dark:bg-[#C8A45C]/20 border border-[#C8A45C]/20 dark:border-[#C8A45C]/30 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-200">
                      <svg className="w-5 h-5 text-[#C8A45C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div className="min-w-0">
                      <Link
                        href={`/forms/${form.id}`}
                        className="text-sm font-semibold text-gray-900 dark:text-white truncate block hover:text-[#003778] dark:hover:text-blue-400 transition-colors"
                      >
                        {form.title}
                      </Link>
                      <p className="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {form.submissions_count ?? 0} respons · {form.fields_count ?? 0} field
                      </p>
                    </div>
                  </div>
                  <span className={`text-xs px-2 py-0.5 rounded-md border font-medium ${STATUS_STYLES[form.status] ?? 'bg-gray-50 text-gray-500 border-gray-200'}`}>
                    {form.status}
                  </span>
                </div>
                {form.description && (
                  <p className="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3 leading-relaxed">{form.description}</p>
                )}
                {isSuperAdmin && form.user && (
                  <p className="text-xs text-gray-400 dark:text-gray-500">oleh {form.user.name}</p>
                )}
              </div>
              <div className="flex items-center gap-px border-t border-gray-100 dark:border-gray-700">
                <Link href={`/forms/${form.id}/edit`}
                  className="flex-1 text-center px-2 py-2.5 rounded-bl-xl text-xs text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-150 inline-flex items-center justify-center gap-1">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit
                </Link>
                <Link href={`/forms/${form.id}/submissions`}
                  className="flex-1 text-center px-2 py-2.5 text-xs text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-150 inline-flex items-center justify-center gap-1">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  Data
                </Link>
                <Link href={`/forms/${form.id}/analytics`}
                  className="flex-1 text-center px-2 py-2.5 text-xs text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-150 inline-flex items-center justify-center gap-1">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                  Analytics
                </Link>
                <button onClick={() => setDeleteId(form.id)}
                  className="flex-1 text-center px-2 py-2.5 rounded-br-xl text-xs text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-150 inline-flex items-center justify-center gap-1">
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Hapus
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-center gap-1.5">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <button key={p} onClick={() => setPage(p)}
              className={`w-8 h-8 rounded-lg text-xs font-medium transition-all duration-150 ${
                p === meta.current_page
                  ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
              }`}>
              {p}
            </button>
          ))}
        </div>
      )}

      <ConfirmDialog
        open={!!deleteId}
        title="Hapus Form"
        message="Apakah Anda yakin ingin menghapus form ini? Semua data submissions akan ikut terhapus."
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        onConfirm={handleDelete}
        onCancel={() => setDeleteId(null)}
      />
    </div>
  );
}
