import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  DocumentTextIcon, PlusIcon, TrashIcon, ClipboardDocumentListIcon,
  ChartBarIcon, MagnifyingGlassIcon, ChevronRightIcon,
} from '@heroicons/react/24/outline';
import { formService } from '../../services';
import StatusBadge from '../../components/StatusBadge';
import Pagination from '../../components/Pagination';
import ConfirmDialog from '../../components/ConfirmDialog';
import LoadingSpinner from '../../components/LoadingSpinner';
import EmptyState from '../../components/EmptyState';

export default function FormsIndex() {
  const navigate = useNavigate();
  const [forms, setForms] = useState<any[]>([]);
  const [meta, setMeta] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [activeTab, setActiveTab] = useState('');
  const [deleteId, setDeleteId] = useState<number | null>(null);

  const tabs = [
    { key: '', label: 'Semua' },
    { key: 'draft', label: 'Draft' },
    { key: 'published', label: 'Published' },
    { key: 'closed', label: 'Closed' },
  ];

  const fetchForms = useCallback(async (page = 1) => {
    setLoading(true);
    try {
      const params: Record<string, any> = { page };
      if (search) params.search = search;
      if (activeTab) params.status = activeTab;
      const res = await formService.list(params);
      setForms(res.data);
      setMeta(res);
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  }, [search, activeTab]);

  useEffect(() => { fetchForms(); }, [fetchForms]);

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await formService.delete(deleteId);
      setDeleteId(null);
      fetchForms();
    } catch { /* ignore */ }
  };

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Forms</h1>
          <p className="text-white/40 text-sm mt-1">Kelola formulir elektronik</p>
        </div>
        <button onClick={() => navigate('/forms/create')} className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white font-semibold text-sm hover:shadow-lg hover:shadow-gold-400/25 transition">
          <PlusIcon className="h-4 w-4" />
          Buat Formulir
        </button>
      </div>

      <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div className="relative w-full sm:w-64">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
          <input type="text" placeholder="Cari formulir..." value={search} onChange={(e) => setSearch(e.target.value)}
            className="w-full pl-9 pr-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
        </div>
        <div className="flex gap-1 bg-white/5 rounded-xl p-1 border border-white/10">
          {tabs.map((tab) => (
            <button key={tab.key} onClick={() => setActiveTab(tab.key)}
              className={`px-3.5 py-1.5 rounded-lg text-sm capitalize transition ${
                activeTab === tab.key ? 'bg-kbb-700/50 text-white font-medium border border-white/10' : 'text-white/40 hover:text-white/60'
              }`}>{tab.label}</button>
          ))}
        </div>
      </div>

      {loading ? (
        <LoadingSpinner />
      ) : forms.length === 0 ? (
        <EmptyState icon={<DocumentTextIcon className="h-12 w-12" />} title="Belum ada formulir" description="Buat formulir pertama Anda untuk memulai"
          action={<button onClick={() => navigate('/forms/create')} className="px-5 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition">Buat Formulir</button>} />
      ) : (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {forms.map((form) => (
              <div key={form.id} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5 hover:border-gold-400/20 hover:shadow-lg hover:shadow-gold-400/5 transition-all duration-300">
                <div className="flex items-start justify-between mb-3">
                  <StatusBadge status={form.status} />
                  <div className="flex gap-1">
                    <button onClick={(e) => { e.stopPropagation(); navigate(`/forms/${form.id}/edit`); }}
                      className="p-1.5 rounded-lg text-white/30 hover:text-gold-400 hover:bg-white/5 transition" title="Edit">
                      <svg className="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button onClick={async (e) => { e.stopPropagation(); try { await formService.duplicate(form.id); fetchForms(); } catch {} }}
                      className="p-1.5 rounded-lg text-white/30 hover:text-gold-400 hover:bg-white/5 transition" title="Duplikat">
                      <svg className="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                    <button onClick={(e) => { e.stopPropagation(); setDeleteId(form.id); }}
                      className="p-1.5 rounded-lg text-white/30 hover:text-red-400 hover:bg-white/5 transition" title="Hapus">
                      <TrashIcon className="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
                <div onClick={() => navigate(`/forms/${form.id}`)} className="cursor-pointer">
                  <h3 className="text-base font-semibold text-white mb-1 truncate">{form.title}</h3>
                  <p className="text-sm text-white/40 line-clamp-2 mb-4 min-h-[2.5rem]">{form.description || 'Tidak ada deskripsi'}</p>
                </div>
                <div className="flex items-center gap-4 text-xs text-white/30 border-t border-white/5 pt-3">
                  <span className="flex items-center gap-1">
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                    {form.fields_count} field
                  </span>
                  <span className="flex items-center gap-1">
                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {form.submissions_count} pengiriman
                  </span>
                </div>
                <div className="flex gap-3 mt-3">
                  <button onClick={() => navigate(`/forms/${form.id}/submissions`)} className="text-xs text-white/40 hover:text-gold-400 font-medium flex items-center gap-1 transition">
                    <ClipboardDocumentListIcon className="h-3.5 w-3.5" /> Data
                  </button>
                  <button onClick={() => navigate(`/forms/${form.id}/analytics`)} className="text-xs text-white/40 hover:text-gold-400 font-medium flex items-center gap-1 transition">
                    <ChartBarIcon className="h-3.5 w-3.5" /> Analitik
                  </button>
                  <button onClick={() => navigate(`/forms/${form.id}`)} className="text-xs text-white/40 hover:text-gold-400 font-medium flex items-center gap-1 transition ml-auto">
                    Detail <ChevronRightIcon className="h-3 w-3" />
                  </button>
                </div>
              </div>
            ))}
          </div>
          {meta && <Pagination currentPage={meta.current_page} lastPage={meta.last_page} total={meta.total} from={meta.from ?? 0} to={meta.to ?? 0} onPageChange={(p) => fetchForms(p)} />}
        </>
      )}

      <ConfirmDialog open={!!deleteId} title="Hapus Formulir" message="Apakah Anda yakin ingin menghapus formulir ini? Tindakan ini tidak dapat dibatalkan." confirmLabel="Hapus" cancelLabel="Batal" variant="danger" onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
    </div>
  );
}
