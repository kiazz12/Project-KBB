import { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { TableCellsIcon, ArrowDownTrayIcon, ArrowLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import { formService } from '../../../services';
import Pagination from '../../../components/Pagination';
import LoadingSpinner from '../../../components/LoadingSpinner';
import EmptyState from '../../../components/EmptyState';

export default function SubmissionsIndex() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [submissions, setSubmissions] = useState<any[]>([]);
  const [meta, setMeta] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');

  const fetch = useCallback(async (page = 1) => {
    setLoading(true);
    try {
      const params: Record<string, any> = { page };
      if (dateFrom) params.date_from = dateFrom;
      if (dateTo) params.date_to = dateTo;
      const res = await formService.getSubmissions(Number(id), params);
      setSubmissions(res.data);
      setMeta(res);
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  }, [id, dateFrom, dateTo]);

  useEffect(() => { fetch(); }, [fetch]);

  const exportCsv = async () => {
    try {
      const res = await formService.exportCsv(Number(id));
      const url = window.URL.createObjectURL(new Blob([res]));
      const a = document.createElement('a');
      a.href = url;
      a.download = `submissions-${id}.csv`;
      a.click();
    } catch { /* ignore */ }
  };

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
            <TableCellsIcon className="h-6 w-6 text-gold-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-white">Data Pengiriman</h1>
            <p className="text-sm text-white/40">Form #{id}</p>
          </div>
        </div>
        <div className="flex gap-2">
          <button onClick={exportCsv} className="px-3 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
            <ArrowDownTrayIcon className="h-4 w-4" /> Export CSV
          </button>
          <button onClick={() => navigate(`/forms/${id}`)} className="px-3 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
            <ArrowLeftIcon className="h-4 w-4" /> Kembali
          </button>
        </div>
      </div>

      <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
        <div>
          <label className="block text-xs text-white/40 uppercase tracking-wider font-medium mb-1.5">Dari Tanggal</label>
          <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)}
            className="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <div>
          <label className="block text-xs text-white/40 uppercase tracking-wider font-medium mb-1.5">Sampai Tanggal</label>
          <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)}
            className="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <button onClick={() => fetch()} className="px-3 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition">Filter</button>
      </div>

      {loading ? (
        <LoadingSpinner />
      ) : submissions.length === 0 ? (
        <EmptyState icon={<TableCellsIcon className="h-12 w-12" />} title="Belum ada pengiriman" description="Formulir ini belum menerima pengiriman data" />
      ) : (
        <div className="overflow-x-auto rounded-2xl border border-white/10 bg-white/5">
          <table className="w-full">
            <thead>
              <tr className="bg-white/5">
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">ID</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">Waktu</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">IP</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">Field Pertama</th>
                <th className="px-4 py-3 text-right text-xs text-white/40 uppercase tracking-wider font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-white/5">
              {submissions.map((s) => (
                <tr key={s.id} onClick={() => navigate(`/forms/${id}/submissions/${s.id}`)}
                  className="hover:bg-white/5 cursor-pointer transition">
                  <td className="px-4 py-3 text-xs font-mono text-white/40">{s.uuid?.slice(0, 8) || `#${s.id}`}...</td>
                  <td className="px-4 py-3 text-xs text-white/70">
                    {new Date(s.submitted_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                  </td>
                  <td className="px-4 py-3 text-xs font-mono text-white/30">{s.ip_address ?? '-'}</td>
                  <td className="px-4 py-3 text-xs text-white/40 truncate max-w-[200px]">{s.first_value ?? '-'}</td>
                  <td className="px-4 py-3 text-right">
                    <span className="text-gold-400 hover:text-gold-300 text-xs font-medium inline-flex items-center gap-1">Detail <ChevronRightIcon className="h-3 w-3" /></span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {meta && <Pagination currentPage={meta.current_page} lastPage={meta.last_page} total={meta.total} from={meta.from} to={meta.to} onPageChange={(p) => fetch(p)} />}
    </div>
  );
}
