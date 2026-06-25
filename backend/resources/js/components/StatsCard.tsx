import type { ReactNode } from 'react';

interface Props {
  icon: ReactNode;
  label: string;
  value: string | number;
  trend?: string;
  trendUp?: boolean;
}

export default function StatsCard({ icon, label, value, trend, trendUp }: Props) {
  return (
    <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5 hover:border-gold-400/20 hover:shadow-lg hover:shadow-gold-400/5 transition-all duration-300">
      <div className="flex items-start justify-between mb-3">
        <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center text-gold-400">
          {icon}
        </div>
        {trend && (
          <span className={`text-xs font-medium flex items-center gap-0.5 ${trendUp ? 'text-emerald-400' : 'text-red-400'}`}>
            <svg className={`w-3 h-3 ${trendUp ? '' : 'rotate-180'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            {trend}
          </span>
        )}
      </div>
      <p className="text-white/40 text-xs font-medium uppercase tracking-wider mb-0.5">{label}</p>
      <p className="text-2xl font-bold text-white">{value}</p>
    </div>
  );
}
