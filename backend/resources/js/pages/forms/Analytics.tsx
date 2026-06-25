import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { formService } from '../../services';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function FormAnalytics({ id }: { id: number }) {
  const [analytics, setAnalytics] = useState<any>(null);
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [a, f] = await Promise.all([
          formService.getAnalytics(id),
          formService.show(id),
        ]);
        setAnalytics(a);
        setForm(f);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [id]);

  if (loading) return <LoadingSpinner />;
  if (!analytics) return <div className="text-center py-12 text-gray-500">Data tidak tersedia</div>;

  const maxCount = analytics.submissions_by_date?.length
    ? Math.max(...analytics.submissions_by_date.map((x: any) => x.count))
    : 1;

  return (
    <div className="space-y-6">
      <nav className="flex items-center gap-2 text-sm text-gray-500">
        <Link href="/forms" className="hover:text-[#003778] transition">Forms</Link>
        <span>/</span>
        <Link href={`/forms/${id}`} className="hover:text-[#003778] transition">{form?.title || 'Form'}</Link>
        <span>/</span>
        <span className="text-gray-900 font-medium">Analytics</span>
      </nav>

      <div className="flex items-center gap-3">
        <Link href={`/forms/${id}`} className="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" aria-label="Kembali ke detail formulir">
          <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </Link>
        <div>
          <h1 className="text-xl font-bold">Analytics</h1>
          <p className="kbb-text-muted text-sm">{form?.title || ''}</p>
        </div>
      </div>

      <div className="kbb-card p-5">
        <p className="kbb-text-muted text-xs uppercase tracking-wider font-medium mb-1">Total Submissions</p>
        <p className="text-3xl font-bold kbb-text-primary">{analytics.total_submissions}</p>
      </div>

      {analytics.submissions_by_date?.length > 0 && (
        <div className="kbb-card p-5">
          <h2 className="text-sm font-semibold mb-4">Submissions per Hari</h2>
          <div className="space-y-2">
            {analytics.submissions_by_date.map((d: any) => (
              <div key={d.date} className="flex items-center gap-3">
                <span className="kbb-text-muted text-sm w-32">{d.date}</span>
                <div className="flex-1 h-6 rounded-lg bg-gray-100 overflow-hidden">
                  <div className="h-full rounded-lg kbb-bg-primary-light transition-all"
                    style={{ width: `${(d.count / maxCount) * 100}%` }} />
                </div>
                <span className="text-sm font-medium w-8 text-right">{d.count}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {analytics.field_analytics?.map((fa: any) => (
        <div key={fa.field_id} className="kbb-card p-5">
          <h3 className="text-sm font-semibold mb-3">{fa.field_label}</h3>
          {Object.keys(fa.counts).length === 0 ? (
            <p className="text-gray-400 text-sm">Belum ada data</p>
          ) : (
            <div className="space-y-2">
              {Object.entries(fa.counts).map(([key, val]) => (
                <div key={key} className="flex items-center gap-3">
                  <span className="text-sm flex-1">{key}</span>
                  <div className="w-32 h-5 rounded-lg bg-gray-100 overflow-hidden">
                    <div className="h-full rounded-lg kbb-bg-primary-light transition-all"
                      style={{ width: `${((val as number) / Math.max(...Object.values(fa.counts) as number[])) * 100}%` }} />
                  </div>
                  <span className="text-sm font-medium w-8 text-right">{val as number}</span>
                </div>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  );
}
