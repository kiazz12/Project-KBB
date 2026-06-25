interface Props {
  status: string;
}

export default function StatusBadge({ status }: Props) {
  const styles: Record<string, string> = {
    published: 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
    draft: 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
    closed: 'bg-white/5 text-white/40 border border-white/10',
  };
  return (
    <span className={`text-xs px-2 py-0.5 rounded-lg ${styles[status] || styles.draft}`}>
      {status}
    </span>
  );
}
