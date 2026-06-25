import { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { dashboardService } from '../services';
import type { Form } from '../types';

interface DashboardStatsData {
  total_forms: number;
  total_submissions: number;
  draft_forms: number;
  published_forms: number;
  submissions_today: number;
}

function Greeting({ name }: { name: string }) {
  const h = new Date().getHours();
  const greeting = h < 10 ? 'Selamat Pagi' : h < 15 ? 'Selamat Siang' : h < 18 ? 'Selamat Sore' : 'Selamat Malam';
  return `${greeting}, ${name}`;
}

function Skeleton() {
  return (
    <div className="space-y-6 animate-pulse">
      <div className="flex items-center justify-between">
        <div className="space-y-2">
          <div className="h-8 bg-gray-200 rounded-lg w-64" />
          <div className="h-4 bg-gray-200 rounded-lg w-40" />
        </div>
        <div className="h-10 bg-gray-200 rounded-xl w-36" />
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div className="flex items-center justify-between mb-3">
              <div className="h-3 bg-gray-200 rounded w-20" />
              <div className="w-10 h-10 bg-gray-200 rounded-lg" />
            </div>
            <div className="h-8 bg-gray-200 rounded w-16" />
          </div>
        ))}
      </div>
      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="px-5 py-4 border-b border-gray-100">
          <div className="h-4 bg-gray-200 rounded w-32" />
        </div>
        <div className="divide-y divide-gray-50">
          {[1, 2, 3].map((i) => (
            <div key={i} className="flex items-center justify-between px-5 py-4">
              <div className="flex items-center gap-4">
                <div className="w-9 h-9 bg-gray-200 rounded-lg" />
                <div className="space-y-1.5">
                  <div className="h-4 bg-gray-200 rounded w-48" />
                  <div className="h-3 bg-gray-200 rounded w-32" />
                </div>
              </div>
              <div className="h-6 bg-gray-200 rounded-full w-20" />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

const statusConfig: Record<string, { label: string; bg: string; text: string; dot: string }> = {
  published: {
    label: 'Published',
    bg: 'bg-emerald-50',
    text: 'text-emerald-700',
    dot: 'bg-emerald-500',
  },
  draft: {
    label: 'Draft',
    bg: 'bg-amber-50',
    text: 'text-amber-700',
    dot: 'bg-amber-500',
  },
  closed: {
    label: 'Closed',
    bg: 'bg-gray-100',
    text: 'text-gray-600',
    dot: 'bg-gray-400',
  },
};

export default function Dashboard() {
  const { auth } = usePage().props as unknown as {
    auth: { user: { name: string; email: string; role: string } };
  };

  const [stats, setStats] = useState<DashboardStatsData | null>(null);
  const [recentForms, setRecentForms] = useState<Form[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [s, f] = await Promise.all([
        dashboardService.getStats(),
        dashboardService.getRecentForms(),
      ]);
      setStats(s as unknown as DashboardStatsData);
      setRecentForms(f);
    } catch {
      setError('Gagal memuat data dashboard. Silakan coba lagi.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  if (loading) return <Skeleton />;

  if (error) {
    return (
      <div role="alert" className="flex flex-col items-center justify-center py-20">
        <div className="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-4">
          <svg className="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
            />
          </svg>
        </div>
        <p className="text-gray-600 mb-4">{error}</p>
        <button
          onClick={fetchData}
          className="kbb-btn kbb-btn-primary"
        >
          Coba Lagi
        </button>
      </div>
    );
  }

  const statCards = [
    {
      label: 'Total Forms',
      value: stats?.total_forms ?? 0,
      iconBg: 'bg-blue-50',
      iconColor: 'text-blue-600',
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
      ),
    },
    {
      label: 'Total Responses',
      value: stats?.total_submissions ?? 0,
      iconBg: 'bg-green-50',
      iconColor: 'text-green-600',
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
          />
        </svg>
      ),
    },
    {
      label: 'Hari Ini',
      value: stats?.submissions_today ?? 0,
      iconBg: 'bg-purple-50',
      iconColor: 'text-purple-600',
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      ),
    },
    {
      label: 'Draft Forms',
      value: stats?.draft_forms ?? 0,
      iconBg: 'bg-amber-50',
      iconColor: 'text-amber-600',
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
      ),
    },
    {
      label: 'Published Forms',
      value: stats?.published_forms ?? 0,
      iconBg: 'bg-emerald-50',
      iconColor: 'text-emerald-600',
      icon: (
        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">
            <Greeting name={auth.user.name} />
          </h1>
          <p className="text-gray-500 text-sm mt-1">Berikut ringkasan dashboard Anda</p>
        </div>
        <Link
          href="/forms/create"
          className="kbb-btn kbb-btn-primary"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
          </svg>
          Buat Form Baru
        </Link>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {statCards.map((card, i) => (
          <div
            key={card.label}
            className="kbb-card p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 animate-fade-in-up"
            style={{ animationDelay: `${i * 60}ms`, animationFillMode: 'both' }}
          >
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                {card.label}
              </span>
              <div
                className={`w-10 h-10 rounded-lg ${card.iconBg} dark:opacity-80 flex items-center justify-center ${card.iconColor}`}
              >
                {card.icon}
              </div>
            </div>
            <p className="text-3xl font-bold text-gray-900 dark:text-white">{card.value}</p>
          </div>
        ))}
      </div>

      <div className="kbb-card animate-fade-in-up" style={{ animationDelay: '240ms', animationFillMode: 'both' }}>
        <div className="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
          <h2 className="text-sm font-semibold text-gray-900 dark:text-white">Formulir Terbaru</h2>
        </div>
        {recentForms.length === 0 ? (
          <div className="text-center py-12">
            <div className="w-14 h-14 mx-auto mb-3 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
              <svg className="w-7 h-7 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <p className="text-sm text-gray-400 dark:text-gray-500">Belum ada formulir. Buat form baru untuk memulai.</p>
          </div>
        ) : (
          <div className="divide-y divide-gray-50 dark:divide-gray-700/50">
            {recentForms.map((form) => {
              const cfg = statusConfig[form.status] ?? {
                label: form.status,
                bg: 'bg-gray-100 dark:bg-gray-700',
                text: 'text-gray-600 dark:text-gray-300',
                dot: 'bg-gray-400',
              };
              return (
                <Link
                  key={form.id}
                  href={`/forms/${form.id}`}
                  className="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-150 group"
                >
                  <div className="flex items-center gap-4 min-w-0">
                    <div className="w-9 h-9 rounded-lg bg-[#C8A45C]/10 dark:bg-[#C8A45C]/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                      <svg className="w-4 h-4 text-[#C8A45C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div className="min-w-0">
                      <p className="text-sm font-medium text-gray-900 dark:text-white truncate group-hover:text-[#003778] dark:group-hover:text-blue-400 transition-colors">
                        {form.title}
                      </p>
                      <p className="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {form.submissions_count ?? 0} respons &middot; {form.fields_count ?? 0} field
                        {form.user && ` \u00B7 ${form.user.name}`}
                      </p>
                    </div>
                  </div>
                  <span className={`text-xs font-medium px-2.5 py-0.5 rounded-full ${cfg.bg} ${cfg.text} flex items-center gap-1.5 flex-shrink-0`}>
                    <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`} />
                    {cfg.label}
                  </span>
                </Link>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
