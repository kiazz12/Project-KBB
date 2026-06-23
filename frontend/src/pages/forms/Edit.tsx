import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  ArrowLeftIcon, EyeIcon, XMarkIcon, TrashIcon,
  ChevronUpIcon, ChevronDownIcon, PlusIcon, Cog6ToothIcon,
} from '@heroicons/react/24/outline';
import { formService } from '../../services';
import StatusBadge from '../../components/StatusBadge';
import LoadingSpinner from '../../components/LoadingSpinner';
import ConfirmDialog from '../../components/ConfirmDialog';

const FIELD_TYPES = [
  { value: 'text', label: 'Teks Pendek', icon: 'Aa' },
  { value: 'textarea', label: 'Teks Panjang', icon: '¶' },
  { value: 'number', label: 'Angka', icon: '#' },
  { value: 'email', label: 'Email', icon: '@' },
  { value: 'phone', label: 'Telepon', icon: 'T' },
  { value: 'date', label: 'Tanggal', icon: 'D' },
  { value: 'time', label: 'Waktu', icon: 'W' },
  { value: 'select', label: 'Dropdown', icon: 'V' },
  { value: 'radio', label: 'Pilihan Ganda', icon: 'O' },
  { value: 'checkbox', label: 'Checkbox', icon: '☑' },
  { value: 'file', label: 'File', icon: 'F' },
  { value: 'rating', label: 'Rating', icon: '★' },
  { value: 'signature', label: 'Tanda Tangan', icon: '✍' },
];

export default function FormEdit() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [selectedFieldId, setSelectedFieldId] = useState<number | null>(null);
  const [showPreview, setShowPreview] = useState(false);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [showFieldPicker, setShowFieldPicker] = useState(false);
  const [deleteFieldId, setDeleteFieldId] = useState<number | null>(null);
  const [error, setError] = useState('');

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

  const updateForm = (partial: any) => {
    if (form) setForm({ ...form, ...partial });
  };

  const handleSave = async () => {
    if (!form) return;
    setSaving(true);
    setError('');
    try {
      await formService.update(form.id, form as any);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal menyimpan');
    } finally {
      setSaving(false);
    }
  };

  const addField = async (type: string) => {
    if (!form) return;
    try {
      const res = await formService.addField(form.id, {
        type: type as any,
        label: FIELD_TYPES.find((t: any) => t.value === type)?.label || type,
        required: false,
        order: form.fields.length + 1,
      });
      setForm({ ...form, fields: [...form.fields, res] });
      setSelectedFieldId(res.id);
      setShowFieldPicker(false);
    } catch { /* ignore */ }
  };

  const updateField = async (field: any) => {
    if (!form) return;
    setForm({ ...form, fields: form.fields.map((f: any) => f.id === field.id ? field : f) });
    try {
      await formService.updateField(form.id, field.id, {
        label: field.label,
        required: field.required,
        options: field.options ?? undefined,
        placeholder: field.placeholder ?? undefined,
      });
    } catch { /* ignore */ }
  };

  const deleteField = async () => {
    if (!form || !deleteFieldId) return;
    try {
      await formService.deleteField(form.id, deleteFieldId);
      setForm({ ...form, fields: form.fields.filter((f: any) => f.id !== deleteFieldId) });
      setSelectedFieldId(null);
      setDeleteFieldId(null);
    } catch { /* ignore */ }
  };

  const moveField = async (fieldId: number, direction: 'up' | 'down') => {
    if (!form) return;
    const idx = form.fields.findIndex((f: any) => f.id === fieldId);
    if (idx === -1) return;
    const newFields = [...form.fields];
    if (direction === 'up' && idx > 0) {
      [newFields[idx - 1], newFields[idx]] = [newFields[idx], newFields[idx - 1]];
    } else if (direction === 'down' && idx < newFields.length - 1) {
      [newFields[idx], newFields[idx + 1]] = [newFields[idx + 1], newFields[idx]];
    } else return;
    const reordered = newFields.map((f: any, i: number) => ({ ...f, order: i + 1 }));
    setForm({ ...form, fields: reordered });
    try {
      await formService.reorderFields(form.id, reordered.map((f: any) => f.id));
    } catch { /* ignore */ }
  };

  const selectedField = form?.fields.find((f: any) => f.id === selectedFieldId) ?? null;

  if (loading) return <LoadingSpinner />;
  if (!form) return <div className="text-center py-12 text-white/50">Form tidak ditemukan</div>;

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-wrap items-center gap-3">
        <button onClick={() => navigate(`/forms/${id}`)} className="p-2 rounded-xl text-white/40 hover:text-white/70 hover:bg-white/5 transition">
          <ArrowLeftIcon className="h-5 w-5" />
        </button>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2">
            <h1 className="text-xl font-bold text-white truncate">{form.title || 'Form Baru'}</h1>
            <StatusBadge status={form.status} />
          </div>
        </div>
        <div className="flex gap-2">
          <button onClick={() => setShowPreview(!showPreview)}
            className="px-3 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
            <EyeIcon className="h-4 w-4" /> {showPreview ? 'Edit' : 'Preview'}
          </button>
          {form.status === 'published' ? (
            <button onClick={async () => { await formService.close(form.id); navigate(`/forms/${id}`); }}
              className="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-sm font-semibold transition">Tutup</button>
          ) : form.status === 'draft' ? (
            <button onClick={async () => { await formService.publish(form.id); navigate(`/forms/${id}`); }}
              className="px-4 py-2 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white text-sm font-semibold hover:shadow-lg hover:shadow-gold-400/25 transition">Publikasikan</button>
          ) : null}
          <button onClick={handleSave} disabled={saving}
            className="px-4 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition shadow-lg shadow-kbb-700/30">
            {saving ? 'Menyimpan...' : 'Simpan'}
          </button>
        </div>
      </div>

      {error && <div className="px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">{error}</div>}

      <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5 space-y-4">
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Judul Formulir</label>
            <input type="text" value={form.title} onChange={(e) => updateForm({ title: e.target.value })} placeholder="Judul formulir"
              className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
          </div>
          <div>
            <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Deskripsi</label>
            <input type="text" value={form.description ?? ''} onChange={(e) => updateForm({ description: e.target.value })} placeholder="Deskripsi formulir"
              className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
          </div>
        </div>
      </div>

      <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
        <button onClick={() => setShowAdvanced(!showAdvanced)}
          className="flex items-center gap-2 text-sm font-medium text-white/50 hover:text-white/70 transition">
          <svg className={`h-4 w-4 transition-transform duration-300 ${showAdvanced ? 'rotate-90' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
          </svg>
          Pengaturan Lanjutan
        </button>
        {showAdvanced && (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4 pt-4 border-t border-white/10 animate-fade-in-up">
            <div>
              <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
              <input type="date" value={form.opens_at ?? ''} onChange={(e) => updateForm({ opens_at: e.target.value || null })}
                className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
            </div>
            <div>
              <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
              <input type="date" value={form.closed_at ?? ''} onChange={(e) => updateForm({ closed_at: e.target.value || null })}
                className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
            </div>
            <div>
              <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Maks. Pengiriman</label>
              <input type="number" value={form.max_submissions ?? ''} onChange={(e) => updateForm({ max_submissions: e.target.value ? Number(e.target.value) : null })} placeholder="Tak terbatas"
                className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
            </div>
            <div className="space-y-3 pt-1">
              {['require_auth', 'limit_one_response', 'collect_ip'].map((key) => (
                <label key={key} className="flex items-center gap-2.5 cursor-pointer">
                  <input type="checkbox" checked={(form as any)[key]} onChange={(e) => updateForm({ [key]: e.target.checked })}
                    className="w-4 h-4 rounded accent-gold-400" />
                  <span className="text-sm text-white/50">
                    {key === 'require_auth' ? 'Harus login' : key === 'limit_one_response' ? 'Batas 1 respon per user' : 'Kumpulkan IP'}
                  </span>
                </label>
              ))}
            </div>
            <div className="sm:col-span-2 lg:col-span-1">
              <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Pesan Konfirmasi</label>
              <textarea value={form.confirmation_message ?? ''} onChange={(e) => updateForm({ confirmation_message: e.target.value })} rows={2} placeholder="Terima kasih telah mengisi formulir ini."
                className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition resize-none" />
            </div>
          </div>
        )}
      </div>

      {showPreview ? (
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 space-y-4">
          <h2 className="text-base font-semibold text-white flex items-center gap-2">
            <EyeIcon className="h-4 w-4 text-gold-400" /> Preview
          </h2>
          <div className="flex items-center gap-3 text-xs text-white/20">
            <span className="flex-1 h-px bg-white/10" />
            <span className="uppercase tracking-wider font-medium">Fields</span>
            <span className="flex-1 h-px bg-white/10" />
          </div>
          {[...form.fields].sort((a: any, b: any) => a.order - b.order).map((field: any) => (
            <div key={field.id}>
              <label className="block text-sm text-white/70 mb-1.5">{field.label}{field.required && <span className="text-red-400 ml-1">*</span>}</label>
              {field.type === 'textarea' ? (
                <textarea rows={3} placeholder={field.placeholder ?? ''} disabled
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white/50 text-sm resize-none" />
              ) : field.type === 'select' ? (
                <select disabled className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white/50 text-sm">
                  <option value="">{field.placeholder || 'Pilih...'}</option>
                  {field.options?.map((opt: any) => <option key={opt}>{opt}</option>)}
                </select>
              ) : field.type === 'radio' || field.type === 'checkbox' ? (
                <div className="space-y-2">
                  {field.options?.map((opt: any) => (
                    <label key={opt} className="flex items-center gap-2 cursor-pointer">
                      <input type={field.type} disabled className="w-4 h-4 accent-gold-400" />
                      <span className="text-sm text-white/50">{opt}</span>
                    </label>
                  ))}
                </div>
              ) : (
                <input type={field.type} placeholder={field.placeholder ?? ''} disabled
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white/50 text-sm" />
              )}
            </div>
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
          <div className="lg:col-span-3 space-y-3">
            <div className="flex items-center justify-between">
              <h2 className="text-sm font-semibold text-white">Fields</h2>
              <div className="relative">
                <button onClick={() => setShowFieldPicker(!showFieldPicker)}
                  className="px-4 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition shadow-lg shadow-kbb-700/30 flex items-center gap-1.5">
                  <PlusIcon className="h-3.5 w-3.5" /> Tambah Field
                </button>
                {showFieldPicker && (
                  <div className="absolute right-0 top-full mt-1.5 w-56 rounded-2xl z-20 overflow-hidden animate-fade-in-up shadow-2xl shadow-black/40"
                    style={{ background: 'rgba(10, 10, 26, 0.98)', border: '1px solid rgba(255,255,255,0.1)' }}>
                    <div className="p-1.5">
                      {FIELD_TYPES.map((t: any) => (
                        <button key={t.value} onClick={() => addField(t.value)}
                          className="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-white/50 hover:text-white/80 rounded-xl transition hover:bg-white/5">
                          <span className="w-7 h-7 rounded-lg bg-gold-400/10 border border-gold-400/20 flex items-center justify-center text-xs font-medium text-gold-400">{t.icon}</span>
                          {t.label}
                        </button>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>

            {form.fields.length === 0 ? (
              <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-10 text-center">
                <p className="text-white/40 text-sm">Belum ada field. Klik "Tambah Field" untuk memulai.</p>
              </div>
            ) : (
              [...form.fields].sort((a: any, b: any) => a.order - b.order).map((field: any, idx: number) => (
                <div key={field.id} onClick={() => setSelectedFieldId(field.id)}
                  className={`bg-kbb-900/80 backdrop-blur-xl border rounded-2xl p-3.5 flex items-center gap-3 cursor-pointer transition-all duration-200 ${
                    selectedFieldId === field.id ? 'border-gold-400/40 shadow-lg shadow-gold-400/10' : 'border-white/10 hover:border-white/20'
                  }`}>
                  <div className="flex flex-col gap-0.5">
                    <button onClick={(e) => { e.stopPropagation(); moveField(field.id, 'up'); }} disabled={idx === 0}
                      className="p-0.5 text-white/30 hover:text-white/60 disabled:opacity-20 transition">
                      <ChevronUpIcon className="h-3 w-3" />
                    </button>
                    <button onClick={(e) => { e.stopPropagation(); moveField(field.id, 'down'); }} disabled={idx === form.fields.length - 1}
                      className="p-0.5 text-white/30 hover:text-white/60 disabled:opacity-20 transition">
                      <ChevronDownIcon className="h-3 w-3" />
                    </button>
                  </div>
                  <div className="w-9 h-9 rounded-lg bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
                    <span className="text-xs font-medium text-gold-400">{FIELD_TYPES.find((t: any) => t.value === field.type)?.icon || '?'}</span>
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-white truncate">{field.label || 'Label belum diisi'}</p>
                    <p className="text-xs text-white/40 mt-0.5">
                      {FIELD_TYPES.find((t: any) => t.value === field.type)?.label}
                      {field.required && <span className="text-gold-400/60 ml-2">Wajib</span>}
                    </p>
                  </div>
                  <button onClick={(e) => { e.stopPropagation(); setDeleteFieldId(field.id); }}
                    className="p-1.5 rounded-lg text-red-400/50 hover:text-red-400 hover:bg-white/5 transition">
                    <XMarkIcon className="h-3.5 w-3.5" />
                  </button>
                </div>
              ))
            )}
          </div>

          <div className="lg:col-span-2">
            {selectedField ? (
              <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5 space-y-4 animate-fade-in-up">
                <div className="flex items-center justify-between pb-3 border-b border-white/10">
                  <div className="flex items-center gap-2">
                    <Cog6ToothIcon className="h-4 w-4 text-gold-400" />
                    <h3 className="text-sm font-semibold text-white">Properti Field</h3>
                  </div>
                  <button onClick={() => setDeleteFieldId(selectedField.id)}
                    className="p-1.5 rounded-lg text-red-400/50 hover:text-red-400 hover:bg-white/5 transition">
                    <TrashIcon className="h-3.5 w-3.5" />
                  </button>
                </div>
                <div>
                  <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Label</label>
                  <input type="text" value={selectedField.label} onChange={(e) => updateField({ ...selectedField, label: e.target.value })} placeholder="Pertanyaan"
                    className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
                </div>
                <div>
                  <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Tipe</label>
                  <div className="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-white/50">
                    <span className="text-gold-400 font-medium">{FIELD_TYPES.find((t: any) => t.value === selectedField.type)?.icon}</span>
                    <span>{FIELD_TYPES.find((t: any) => t.value === selectedField.type)?.label}</span>
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Placeholder</label>
                  <input type="text" value={selectedField.placeholder ?? ''} onChange={(e) => updateField({ ...selectedField, placeholder: e.target.value })} placeholder="Text hint"
                    className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
                </div>
                {['select', 'radio', 'checkbox'].includes(selectedField.type) && (
                  <div>
                    <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Opsi (satu per baris)</label>
                    <textarea value={(selectedField.options ?? []).join('\n')} onChange={(e) => updateField({ ...selectedField, options: e.target.value.split('\n').filter(Boolean) })} rows={4} placeholder="Opsi 1"
                      className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition resize-none" />
                  </div>
                )}
                <label className="flex items-center gap-2.5 cursor-pointer pt-1">
                  <input type="checkbox" checked={selectedField.required} onChange={(e) => updateField({ ...selectedField, required: e.target.checked })}
                    className="w-4 h-4 rounded accent-gold-400" />
                  <span className="text-sm text-white/50">Wajib diisi</span>
                </label>
              </div>
            ) : (
              <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 text-center">
                <div className="w-12 h-12 mx-auto mb-3 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center">
                  <Cog6ToothIcon className="h-6 w-6 text-white/30" />
                </div>
                <p className="text-white/40 text-sm">Pilih field untuk mengedit properti</p>
              </div>
            )}
          </div>
        </div>
      )}

      <ConfirmDialog open={!!deleteFieldId} title="Hapus Field" message="Apakah Anda yakin ingin menghapus field ini?" confirmLabel="Hapus" cancelLabel="Batal" variant="danger" onConfirm={deleteField} onCancel={() => setDeleteFieldId(null)} />
    </div>
  );
}
