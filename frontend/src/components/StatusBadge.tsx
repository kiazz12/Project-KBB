interface Props {
  status: string;
}

const dotMap: Record<string, string> = {
  draft: 'bg-white/30',
  published: 'bg-emerald-400',
  closed: 'bg-red-400',
};

const badgeMap: Record<string, string> = {
  draft: 'bg-white/5 text-white/50 border-white/10',
  published: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
  closed: 'bg-red-500/10 text-red-400 border-red-500/20',
};

export default function StatusBadge({ status }: Props) {
  return (
    <span className={`inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider border ${badgeMap[status] || 'bg-white/5 text-white/50 border-white/10'}`}>
      <span className={`w-1.5 h-1.5 rounded-full ${dotMap[status] || 'bg-white/30'}`} />
      {status ? status.replace('_', ' ') : 'unknown'}
    </span>
  );
}
