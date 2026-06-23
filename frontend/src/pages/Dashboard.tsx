import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { DocumentTextIcon, CheckCircleIcon, CalendarDaysIcon, ArrowTrendingUpIcon, PlusIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import { formService } from '../services';
import StatsCard from '../components/StatsCard';
import StatusBadge from '../components/StatusBadge';
import LoadingSpinner from '../components/LoadingSpinner';

export default function Dashboard() {
  const navigate = useNavigate();
  const [stats, setStats] = useState<any>(null);
  const [recentForms, setRecentForms] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const [statsRes, formsRes] = await Promise.all([
          formService.getStats(),
          formService.getRecentForms(),
        ]);
        setStats(statsRes);
        setRecentForms(formsRes);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, []);

  if (loading) return <LoadingSpinner />;

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-white">Dashboard</h1>
          <p className="text-white/40 text-sm mt-1">Overview sistem formulir elektronik KBB</p>
        </div>
        <button onClick={() => navigate('/forms/create')} className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white font-semibold text-sm hover:shadow-lg hover:shadow-gold-400/25 transition">
          <PlusIcon className="h-4 w-4" />
          Buat Formulir
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatsCard icon={<DocumentTextIcon className="h-5 w-5" />} label="Total Formulir" value={stats?.total_forms ?? 0} />
        <StatsCard icon={<CheckCircleIcon className="h-5 w-5" />} label="Dipublikasikan" value={stats?.published_count ?? 0} />
        <StatsCard icon={<CalendarDaysIcon className="h-5 w-5" />} label="Pengiriman Hari Ini" value={stats?.submissions_today ?? 0} />
        <StatsCard icon={<ArrowTrendingUpIcon className="h-5 w-5" />} label="Total Pengiriman" value={stats?.total_submissions ?? 0} />
      </div>

      <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-base font-semibold text-white">Formulir Terbaru</h2>
          <button onClick={() => navigate('/forms')} className="text-xs text-gold-400 hover:text-gold-300 font-medium flex items-center gap-1">
            Lihat Semua <ChevronRightIcon className="h-3 w-3" />
          </button>
        </div>
        {recentForms.length === 0 ? (
          <div className="flex flex-col items-center py-12">
            <div className="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-3">
              <DocumentTextIcon className="h-6 w-6 text-white/30" />
            </div>
            <p className="text-white/40 text-sm mb-4">Belum ada formulir</p>
            <button onClick={() => navigate('/forms/create')} className="px-5 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition shadow-lg shadow-kbb-700/30">
              Buat Formulir Pertama
            </button>
          </div>
        ) : (
          <div className="space-y-1">
            {recentForms.map((form) => (
              <div key={form.id} onClick={() => navigate(`/forms/${form.id}`)}
                className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/5 cursor-pointer transition">
                <div className="w-9 h-9 rounded-lg bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
                  <DocumentTextIcon className="h-5 w-5 text-gold-400" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-white truncate">{form.title}</p>
                  <p className="text-xs text-white/40">{form.submissions_count} pengiriman</p>
                </div>
                <StatusBadge status={form.status} />
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
