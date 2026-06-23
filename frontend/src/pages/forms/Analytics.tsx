import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ChartBarIcon, ArrowLeftIcon, ClockIcon, UsersIcon, TableCellsIcon, CalendarDaysIcon } from '@heroicons/react/24/outline';
import { formService } from '../../services';
import LoadingSpinner from '../../components/LoadingSpinner';

export default function FormAnalytics() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.getAnalytics(Number(id));
        setData(res);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [id]);

  if (loading) return <LoadingSpinner />;
  if (!data) return <div className="text-center py-12 text-white/50">Data tidak tersedia</div>;

  const sbd: Record<string, number> = data.submissions_by_date ?? {};
  const dates = Object.keys(sbd).sort();
  const maxCount = Math.max(...Object.values(sbd), 1);

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
            <ChartBarIcon className="h-6 w-6 text-gold-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-white">Analitik</h1>
            <p className="text-sm text-white/40">{data.form_title}</p>
          </div>
        </div>
        <button onClick={() => navigate(`/forms/${id}`)} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <ArrowLeftIcon className="h-4 w-4" /> Kembali
        </button>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
          <div className="flex items-center gap-2 mb-1">
            <TableCellsIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">Total</span>
          </div>
          <p className="text-2xl font-bold text-white">{data.total_submissions}</p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
          <div className="flex items-center gap-2 mb-1">
            <CalendarDaysIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">Hari Ini</span>
          </div>
          <p className="text-2xl font-bold text-white">{data.submissions_today}</p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
          <div className="flex items-center gap-2 mb-1">
            <UsersIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">Field</span>
          </div>
          <p className="text-2xl font-bold text-white">{Object.keys(data.field_analytics ?? {}).length}</p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
          <div className="flex items-center gap-2 mb-1">
            <ClockIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">Hari Terakhir</span>
          </div>
          <p className="text-2xl font-bold text-white">{data.submissions_today}</p>
        </div>
      </div>

      {dates.length > 0 && (
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
          <h2 className="text-sm font-semibold text-white mb-4 flex items-center gap-2">
            <CalendarDaysIcon className="h-4 w-4 text-gold-400" /> Pengiriman per Tanggal
          </h2>
          <div className="flex items-end gap-1 h-48">
            {dates.map((date) => {
              const count = data.submissions_by_date[date];
              const h = (count / maxCount) * 100;
              return (
                <div key={date} className="flex-1 flex flex-col items-center gap-1 justify-end">
                  <span className="text-white/50 text-xs font-medium">{count}</span>
                  <div className="w-full rounded-t-lg transition-all duration-700 min-h-[4px] bg-gradient-to-t from-gold-600/80 to-gold-400/60" style={{ height: `${Math.max(h, 4)}%` }} />
                  <span className="text-white/30 text-[10px] whitespace-nowrap">
                    {new Date(date + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                  </span>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {Object.entries(data.field_analytics ?? {}).map(([fieldName, options]: any) => {
        const opts: Record<string, number> = options ?? {};
        const total = Object.values(opts).reduce((a: number, b: number) => a + b, 0);
        return (
          <div key={fieldName} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
            <h3 className="text-sm font-semibold text-white mb-4">{fieldName}</h3>
            {Object.entries(opts).map(([option, count]: any) => {
              const pct = total > 0 ? (count / total) * 100 : 0;
              return (
                <div key={option} className="mb-3">
                  <div className="flex justify-between items-center mb-1.5">
                    <span className="text-sm text-white/50">{option}</span>
                    <span className="text-xs text-white/40">{count} ({Math.round(pct)}%)</span>
                  </div>
                  <div className="h-2 bg-white/5 rounded-full overflow-hidden">
                    <div className="h-full rounded-full transition-all bg-gradient-to-r from-gold-400/80 to-gold-400/40" style={{ width: `${pct}%` }} />
                  </div>
                </div>
              );
            })}
          </div>
        );
      })}
    </div>
  );
}
