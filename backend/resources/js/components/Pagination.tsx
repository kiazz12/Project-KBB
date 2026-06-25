interface Props {
  currentPage: number;
  lastPage: number;
  total: number;
  from: number;
  to: number;
  onPageChange: (page: number) => void;
}

export default function Pagination({ currentPage, lastPage, total, from, to, onPageChange }: Props) {
  if (lastPage <= 1) return null;

  const pages: number[] = [];
  const start = Math.max(1, currentPage - 1);
  const end = Math.min(lastPage, currentPage + 1);
  for (let i = start; i <= end; i++) pages.push(i);

  return (
    <div className="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
      <p className="text-xs text-white/40">Menampilkan {from}–{to} dari {total}</p>
      <div className="flex gap-1.5">
        <button onClick={() => onPageChange(currentPage - 1)} disabled={currentPage <= 1}
          className="px-2.5 py-1.5 rounded-lg text-xs text-white/40 hover:text-white/70 hover:bg-white/5 transition disabled:opacity-30 disabled:cursor-not-allowed">
          <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        {start > 1 && (
          <>
            <button onClick={() => onPageChange(1)} className="px-2.5 py-1.5 rounded-lg text-xs text-white/40 hover:text-white/70 hover:bg-white/5 transition">1</button>
            {start > 2 && <span className="flex items-center text-white/20 text-xs px-1">...</span>}
          </>
        )}
        {pages.map((p) => (
          <button key={p} onClick={() => onPageChange(p)}
            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition ${
              p === currentPage ? 'bg-gold-400/20 text-gold-400 border border-gold-400/30' : 'text-white/40 hover:text-white/70 hover:bg-white/5'
            }`}>{p}</button>
        ))}
        {end < lastPage && (
          <>
            {end < lastPage - 1 && <span className="flex items-center text-white/20 text-xs px-1">...</span>}
            <button onClick={() => onPageChange(lastPage)} className="px-2.5 py-1.5 rounded-lg text-xs text-white/40 hover:text-white/70 hover:bg-white/5 transition">{lastPage}</button>
          </>
        )}
        <button onClick={() => onPageChange(currentPage + 1)} disabled={currentPage >= lastPage}
          className="px-2.5 py-1.5 rounded-lg text-xs text-white/40 hover:text-white/70 hover:bg-white/5 transition disabled:opacity-30 disabled:cursor-not-allowed">
          <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  );
}
