import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  DocumentTextIcon, PencilSquareIcon, ClipboardDocumentListIcon,
  ChartBarIcon, ArrowTopRightOnSquareIcon, DocumentDuplicateIcon,
  TrashIcon, CheckIcon, ClockIcon, QueueListIcon,
  UserGroupIcon, EyeSlashIcon, GlobeAltIcon, LinkIcon,
} from '@heroicons/react/24/outline';
import { formService } from '../../services';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import ConfirmDialog from '../../components/ConfirmDialog';

export default function FormShow() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [copied, setCopied] = useState(false);
  const [showDelete, setShowDelete] = useState(false);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.show(Number(id));
        setForm(res);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [id]);

  const handlePublish = async () => {
    try {
      await formService.publish(Number(id));
      const res = await formService.show(Number(id));
      setForm(res);
    } catch { /* ignore */ }
  };

  const handleClose = async () => {
    try {
      await formService.close(Number(id));
      const res = await formService.show(Number(id));
      setForm(res);
    } catch { /* ignore */ }
  };

  const handleDuplicate = async () => {
    try {
      const res = await formService.duplicate(Number(id));
      navigate(`/forms/${res.id}/edit`);
    } catch { /* ignore */ }
  };

  const handleDelete = async () => {
    try {
      await formService.delete(Number(id));
      navigate('/forms');
    } catch { /* ignore */ }
  };

  const copyUrl = () => {
    navigator.clipboard.writeText(`${window.location.origin}/form/${form?.slug}`);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  if (loading) return <LoadingSpinner />;
  if (!form) return <div className="text-center py-12 text-white/50">Form tidak ditemukan</div>;

  const publicUrl = `${window.location.origin}/form/${form.slug}`;
  const statItems = [
    { label: 'Total Field', value: form.fields_count, icon: QueueListIcon },
    { label: 'Pengiriman', value: form.submissions_count, icon: ClipboardDocumentListIcon },
    { label: 'Max. Pengiriman', value: form.max_submissions ?? 'Tak terbatas', icon: EyeSlashIcon },
    { label: 'Wajib Auth', value: form.require_auth ? 'Ya' : 'Tidak', icon: UserGroupIcon },
  ];

  return (
    <div className="space-y-6 max-w-4xl mx-auto animate-fade-in-up">
      <div className="flex items-center gap-2 text-xs text-white/30 mb-2">
        <button onClick={() => navigate('/forms')} className="hover:text-gold-400 transition">Forms</button>
        <span>/</span>
        <span className="text-white/50">{form.title}</span>
      </div>

      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
            <DocumentTextIcon className="h-7 w-7 text-gold-400" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-bold text-white">{form.title}</h1>
              <StatusBadge status={form.status} />
            </div>
            {form.description && <p className="text-white/40 text-sm mt-0.5">{form.description}</p>}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {statItems.map((item) => {
          const Icon = item.icon;
          return (
            <div key={item.label} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
              <div className="flex items-center gap-2 mb-2">
                <Icon className="h-4 w-4 text-white/30" />
                <span className="text-xs text-white/40 uppercase tracking-wider font-medium">{item.label}</span>
              </div>
              <p className="text-2xl font-bold text-white">{item.value}</p>
            </div>
          );
        })}
      </div>

      {form.status === 'published' && (
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
          <div className="flex items-center gap-2 mb-2">
            <GlobeAltIcon className="h-4 w-4 text-emerald-400" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">URL Publik</span>
          </div>
          <div className="flex gap-2">
            <input type="text" value={publicUrl} readOnly
              className="flex-1 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm" />
            <button onClick={copyUrl}
              className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5 whitespace-nowrap">
              {copied ? <CheckIcon className="h-4 w-4 text-emerald-400" /> : <LinkIcon className="h-4 w-4" />}
              {copied ? 'Tersalin' : 'Salin'}
            </button>
            <a href={publicUrl} target="_blank" rel="noopener noreferrer"
              className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
              <ArrowTopRightOnSquareIcon className="h-4 w-4" /> Buka
            </a>
          </div>
        </div>
      )}

      <div className="flex flex-wrap gap-2">
        <button onClick={() => navigate(`/forms/${id}/edit`)} className="px-4 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition shadow-lg shadow-kbb-700/30 flex items-center gap-1.5">
          <PencilSquareIcon className="h-4 w-4" /> Edit
        </button>
        <button onClick={() => navigate(`/forms/${id}/submissions`)} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <ClipboardDocumentListIcon className="h-4 w-4" /> Data
        </button>
        <button onClick={() => navigate(`/forms/${id}/analytics`)} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <ChartBarIcon className="h-4 w-4" /> Analitik
        </button>
        {form.status === 'published' && (
          <button onClick={handleClose} className="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold transition flex items-center gap-1.5">
            <ClockIcon className="h-4 w-4" /> Tutup
          </button>
        )}
        {form.status === 'closed' && (
          <button onClick={handlePublish} className="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white text-sm font-semibold hover:shadow-lg hover:shadow-gold-400/25 transition flex items-center gap-1.5">
            <ArrowTopRightOnSquareIcon className="h-4 w-4" /> Publikasikan Ulang
          </button>
        )}
        {form.status === 'draft' && (
          <button onClick={handlePublish} className="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white text-sm font-semibold hover:shadow-lg hover:shadow-gold-400/25 transition flex items-center gap-1.5">
            <ArrowTopRightOnSquareIcon className="h-4 w-4" /> Publikasikan
          </button>
        )}
        <button onClick={handleDuplicate} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <DocumentDuplicateIcon className="h-4 w-4" /> Duplikat
        </button>
        <button onClick={() => setShowDelete(true)} className="px-4 py-2 rounded-xl text-sm text-red-400/50 hover:text-red-400 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <TrashIcon className="h-4 w-4" /> Hapus
        </button>
      </div>

      <ConfirmDialog open={showDelete} title="Hapus Formulir" message="Apakah Anda yakin ingin menghapus formulir ini?" confirmLabel="Hapus" cancelLabel="Batal" variant="danger" onConfirm={handleDelete} onCancel={() => setShowDelete(false)} />
    </div>
  );
}
