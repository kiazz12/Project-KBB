import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { formService } from '../../../services';
import LoadingSpinner from '../../../components/LoadingSpinner';

export default function SubmissionsIndex({ formId }: { formId: number }) {
  const [form, setForm] = useState<any>(null);
  const [submissions, setSubmissions] = useState<any[]>([]);
  const [meta, setMeta] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [exportError, setExportError] = useState('');

  const fetch = async () => {
    setLoading(true);
    try {
      const [f, s] = await Promise.all([
        formService.show(formId),
        formService.getSubmissions(formId, { page, per_page: 15 }),
      ]);
      setForm(f);
      setSubmissions(s.data);
      setMeta({ current_page: s.current_page, last_page: s.last_page, total: s.total });
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetch(); }, [formId, page]);

  const handleExport = async () => {
    setExportError('');
    try {
      const blob = await formService.exportCsv(formId);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = `${form?.slug || 'form'}-submissions.csv`; a.click();
      URL.revokeObjectURL(url);
    } catch { setExportError('Gagal mengekspor CSV.'); }
  };

  const handleExportPdf = async () => {
    setExportError('');
    try {
      const blob = await formService.exportPdf(formId);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = `${form?.slug || 'form'}-submissions.pdf`; a.click();
      URL.revokeObjectURL(url);
    } catch { setExportError('Gagal mengekspor PDF.'); }
  };

  if (loading && submissions.length === 0) return <LoadingSpinner />;

  return (
    <div className="space-y-6">
      <nav className="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <Link href="/forms" className="hover:text-[#003778] dark:hover:text-blue-400 transition">Forms</Link>
        <span>/</span>
        <Link href={`/forms/${formId}`} className="hover:text-[#003778] dark:hover:text-blue-400 transition">{form?.title || 'Form'}</Link>
        <span>/</span>
        <span className="text-gray-900 dark:text-white font-medium">Data</span>
      </nav>

      <div className="flex flex-wrap items-center justify-between gap-4 animate-fade-in-up">
        <div className="flex items-center gap-3">
          <Link href={`/forms/${formId}`} className="p-2 rounded-lg text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition" aria-label="Kembali ke detail formulir">
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          </Link>
          <div>
            <h1 className="text-xl font-bold text-gray-900 dark:text-white">Data Responses</h1>
            <p className="text-sm text-gray-500 dark:text-gray-400">{form?.title || ''} ({meta?.total ?? 0})</p>
          </div>
        </div>
        <div className="flex gap-2">
          <button onClick={handleExport} className="kbb-btn kbb-btn-primary">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            CSV
          </button>
          <button onClick={handleExportPdf} className="kbb-btn kbb-btn-danger">
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
            PDF
          </button>
        </div>
      </div>

      {exportError && (
        <div role="alert" className="px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm flex items-center gap-2 animate-shake">
          <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          {exportError}
        </div>
      )}

      {submissions.length === 0 ? (
        <div className="kbb-card p-10 text-center animate-fade-in-up">
          <p className="text-gray-400 dark:text-gray-500 text-sm">Belum ada responses.</p>
        </div>
      ) : (
        <div className="kbb-table-wrapper animate-fade-in-up">
          <table className="kbb-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>UUID</th>
                <th>Dikirim</th>
                <th className="text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
              {submissions.map((sub: any) => (
                <tr key={sub.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                  <td className="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">#{sub.id}</td>
                  <td className="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{sub.uuid?.substring(0, 12)}...</td>
                  <td className="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{new Date(sub.submitted_at).toLocaleString('id-ID')}</td>
                  <td className="px-4 py-3 text-right">
                    <Link href={`/forms/${formId}/submissions/${sub.id}`} className="kbb-link text-xs px-3 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition font-medium">Detail</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <div className="flex justify-center gap-1.5">
          {Array.from({ length: meta.last_page }, (_, i) => i + 1).map((p) => (
            <button key={p} onClick={() => setPage(p)} className={`w-8 h-8 rounded-lg text-xs font-medium transition-all duration-150 ${
              p === meta.current_page ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700'
            }`}>{p}</button>
          ))}
        </div>
      )}
    </div>
  );
}
