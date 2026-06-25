import { useState, useEffect, useRef } from 'react';
import { Link, router } from '@inertiajs/react';
import { formService } from '../../services';
import ConfirmDialog from '../../components/ConfirmDialog';
import LoadingSpinner from '../../components/LoadingSpinner';

const FIELD_TYPES = [
  { value: 'text', label: 'Teks Pendek', icon: 'Aa' },
  { value: 'textarea', label: 'Teks Panjang', icon: '¶' },
  { value: 'number', label: 'Angka', icon: '#' },
  { value: 'email', label: 'Email', icon: '@' },
  { value: 'date', label: 'Tanggal', icon: 'D' },
  { value: 'time', label: 'Waktu', icon: 'W' },
  { value: 'select', label: 'Dropdown', icon: 'V' },
  { value: 'radio', label: 'Pilihan Ganda', icon: 'O' },
  { value: 'checkbox', label: 'Checkbox', icon: '☑' },
  { value: 'file', label: 'File', icon: 'F' },
  { value: 'signature', label: 'Tanda Tangan', icon: '✍' },
  { value: 'heading', label: 'Judul', icon: 'H' },
  { value: 'paragraph', label: 'Paragraf', icon: '¶' },
];

const FIELD_ICONS: Record<string, string> = {
  text: 'Aa',
  textarea: '¶',
  number: '#',
  email: '@',
  date: '📅',
  time: '⏰',
  select: '▼',
  radio: '◉',
  checkbox: '☑',
  file: '📎',
  signature: '✍',
  heading: 'H',
  paragraph: '¶',
};

const TYPE_COLORS: Record<string, string> = {
  text: 'bg-blue-50 text-blue-600 border-blue-200',
  textarea: 'bg-purple-50 text-purple-600 border-purple-200',
  number: 'bg-orange-50 text-orange-600 border-orange-200',
  email: 'bg-rose-50 text-rose-600 border-rose-200',
  date: 'bg-teal-50 text-teal-600 border-teal-200',
  time: 'bg-cyan-50 text-cyan-600 border-cyan-200',
  select: 'bg-violet-50 text-violet-600 border-violet-200',
  radio: 'bg-pink-50 text-pink-600 border-pink-200',
  checkbox: 'bg-indigo-50 text-indigo-600 border-indigo-200',
  file: 'bg-amber-50 text-amber-600 border-amber-200',
  signature: 'bg-emerald-50 text-emerald-600 border-emerald-200',
  heading: 'bg-gray-100 text-gray-700 border-gray-300',
  paragraph: 'bg-gray-50 text-gray-500 border-gray-200',
};

interface Props {
  form: any;
}

export default function FormEdit({ form: initialForm }: Props) {
  const [form, setForm] = useState<any>(initialForm);
  const [loading, setLoading] = useState(!initialForm);
  const [saving, setSaving] = useState(false);
  const [selectedFieldId, setSelectedFieldId] = useState<number | null>(null);
  const [showFieldPicker, setShowFieldPicker] = useState(false);
  const [deleteFieldId, setDeleteFieldId] = useState<number | null>(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [activeTab, setActiveTab] = useState<'fields' | 'settings'>('fields');
  const pickerRef = useRef<HTMLDivElement>(null);

  const formId = form?.id;
  const sortedFields = [...(form?.fields || [])].sort((a: any, b: any) => a.order - b.order);
  const selectedField = form?.fields?.find((f: any) => f.id === selectedFieldId) ?? null;

  useEffect(() => {
    if (!form && formId) {
      const fetch = async () => {
        try {
          const res = await formService.show(formId);
          setForm(res);
        } catch { } finally {
          setLoading(false);
        }
      };
      fetch();
    }
  }, [formId]);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (pickerRef.current && !pickerRef.current.contains(e.target as Node)) {
        setShowFieldPicker(false);
      }
    };
    if (showFieldPicker) document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [showFieldPicker]);

  const updateForm = (partial: any) => {
    if (form) setForm({ ...form, ...partial });
  };

  const handleSave = async () => {
    if (!form) return;
    setSaving(true);
    setError('');
    setSuccess('');
    try {
      await formService.update(form.id, form);
      setSuccess('Perubahan berhasil disimpan');
      setTimeout(() => setSuccess(''), 3000);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal menyimpan');
    } finally {
      setSaving(false);
    }
  };

  const handlePublish = async () => {
    if (!form) return;
    try {
      const res = await formService.publish(form.id);
      setForm({ ...form, status: 'published' });
    } catch { }
  };

  const handleClose = async () => {
    if (!form) return;
    try {
      await formService.close(form.id);
      setForm({ ...form, status: 'closed' });
    } catch { }
  };

  const addField = async (type: string) => {
    if (!form) return;
    try {
      const res = await formService.addField(form.id, {
        type: type as any,
        label: FIELD_TYPES.find((t) => t.value === type)?.label || type,
        required: false,
        order: form.fields.length + 1,
      });
      setForm({ ...form, fields: [...form.fields, res] });
      setSelectedFieldId(res.id);
      setShowFieldPicker(false);
    } catch { }
  };

  const updateField = async (field: any) => {
    if (!form) return;
    setForm({ ...form, fields: form.fields.map((f: any) => (f.id === field.id ? field : f)) });
    try {
      await formService.updateField(form.id, field.id, {
        type: field.type,
        label: field.label,
        required: field.required,
        options: field.options ?? undefined,
        placeholder: field.placeholder ?? undefined,
      });
    } catch { }
  };

  const deleteField = async () => {
    if (!form || !deleteFieldId) return;
    try {
      await formService.deleteField(form.id, deleteFieldId);
      setForm({ ...form, fields: form.fields.filter((f: any) => f.id !== deleteFieldId) });
      if (selectedFieldId === deleteFieldId) setSelectedFieldId(null);
      setDeleteFieldId(null);
    } catch { }
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
    } catch { }
  };

  const addOption = (field: any) => {
    const newOpt = `Opsi ${(field.options || []).length + 1}`;
    updateField({ ...field, options: [...(field.options || []), newOpt] });
  };

  const removeOption = (field: any, index: number) => {
    const opts = [...(field.options || [])];
    opts.splice(index, 1);
    updateField({ ...field, options: opts });
  };

  const updateOption = (field: any, index: number, value: string) => {
    const opts = [...(field.options || [])];
    opts[index] = value;
    updateField({ ...field, options: opts });
  };

  const renderFieldPreview = (field: any) => {
    const baseInput = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white';
    if (field.type === 'heading') {
      return <h3 className="text-lg font-bold text-gray-800">{field.label || 'Judul'}</h3>;
    }
    if (field.type === 'paragraph') {
      return <p className="text-sm text-gray-500">{field.label || 'Teks paragraf'}</p>;
    }
    if (field.type === 'textarea') {
      return <textarea rows={3} placeholder={field.placeholder || ''} disabled className={`${baseInput} resize-none opacity-70`} />;
    }
    if (field.type === 'select') {
      return (
        <select disabled className={`${baseInput} appearance-none opacity-70`}>
          <option>{field.placeholder || 'Pilih...'}</option>
          {(field.options || []).map((o: string, i: number) => (
            <option key={i}>{o}</option>
          ))}
        </select>
      );
    }
    if (field.type === 'radio') {
      return (
        <div className="space-y-1.5">
          {(field.options || []).length === 0 && <p className="text-xs text-gray-400 italic">Belum ada opsi</p>}
          {(field.options || []).map((o: string, i: number) => (
            <label key={i} className="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
              <input type="radio" disabled className="accent-kbb-700" />
              {o}
            </label>
          ))}
        </div>
      );
    }
    if (field.type === 'checkbox') {
      return (
        <div className="space-y-1.5">
          {(field.options || []).length === 0 && <p className="text-xs text-gray-400 italic">Belum ada opsi</p>}
          {(field.options || []).map((o: string, i: number) => (
            <label key={i} className="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
              <input type="checkbox" disabled className="accent-kbb-700" />
              {o}
            </label>
          ))}
        </div>
      );
    }
    if (field.type === 'file') {
      return (
        <div className="border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 text-center text-sm text-gray-400">
          📎 Upload File
        </div>
      );
    }
    if (field.type === 'signature') {
      return (
        <div className="border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 text-center text-sm text-gray-400">
          ✍ Tanda Tangan
        </div>
      );
    }
    if (field.type === 'date') {
      return <input type="date" disabled className={`${baseInput} opacity-70`} />;
    }
    if (field.type === 'time') {
      return <input type="time" disabled className={`${baseInput} opacity-70`} />;
    }
    return (
      <input
        type={field.type === 'number' || field.type === 'email' ? field.type : 'text'}
        placeholder={field.placeholder || ''}
        disabled
        className={`${baseInput} opacity-70`}
      />
    );
  };

  const renderFieldSettings = (field: any) => {
    const hasOptions = ['select', 'radio', 'checkbox'].includes(field.type);
    const showPlaceholder = !hasOptions && !['heading', 'paragraph', 'file', 'signature'].includes(field.type);
    return (
      <div className="space-y-4 pt-3">
        <div>
          <label className="kbb-label">Label</label>
          <input
            type="text"
            value={field.label}
            onChange={(e) => updateField({ ...field, label: e.target.value })}
            placeholder="Teks pertanyaan"
            className="kbb-input"
          />
        </div>
        {showPlaceholder && (
          <div>
            <label className="kbb-label">Placeholder</label>
            <input
              type="text"
              value={field.placeholder || ''}
              onChange={(e) => updateField({ ...field, placeholder: e.target.value })}
              placeholder="Teks petunjuk"
              className="kbb-input"
            />
          </div>
        )}
        {hasOptions && (
          <div>
            <label className="kbb-label">Opsi</label>
            <div className="space-y-2">
              {(field.options || []).map((opt: string, i: number) => (
                <div key={i} className="flex items-center gap-2">
                  <span className="text-gray-400 text-sm w-5">{i + 1}.</span>
                  <input
                    type="text"
                    value={opt}
                    onChange={(e) => updateOption(field, i, e.target.value)}
                    placeholder={`Opsi ${i + 1}`}
                    className="kbb-input flex-1"
                  />
                  <button
                    onClick={() => removeOption(field, i)}
                    className="p-1.5 text-gray-400 hover:text-red-500 transition rounded hover:bg-red-50 flex-shrink-0"
                    title="Hapus opsi" aria-label="Hapus opsi"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              ))}
              <button
                onClick={() => addOption(field)}
                className="text-sm text-kbb-700 hover:text-kbb-800 font-medium transition flex items-center gap-1.5"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah opsi
              </button>
            </div>
          </div>
        )}
        {!['heading', 'paragraph'].includes(field.type) && (
          <label className="flex items-center gap-2.5 cursor-pointer pt-1">
            <input
              type="checkbox"
              checked={field.required}
              onChange={(e) => updateField({ ...field, required: e.target.checked })}
              className="w-4 h-4 rounded accent-kbb-700"
            />
            <span className="text-sm text-gray-600">Wajib diisi</span>
          </label>
        )}
      </div>
    );
  };

  const statusBadge = (status: string) => {
    const map: Record<string, string> = {
      draft: 'kbb-badge kbb-badge-warning',
      published: 'kbb-badge kbb-badge-success',
      closed: 'kbb-badge kbb-badge-neutral',
    };
    return <span className={map[status] || map.draft}>{status}</span>;
  };

  const renderFieldCard = (field: any, idx: number) => {
    const isSelected = selectedFieldId === field.id;
    const typeInfo = FIELD_TYPES.find((t) => t.value === field.type);
    return (
      <div
        key={field.id}
        className={`kbb-card overflow-hidden transition-all duration-200 ${
          isSelected ? 'ring-2 ring-kbb-700/20 border-kbb-700/30 shadow-md' : ''
        }`}
      >
        {/* Card Header - always visible */}
        <div
          className="flex items-center gap-2 px-4 pt-3 pb-0 cursor-pointer select-none"
          onClick={() => setSelectedFieldId(isSelected ? null : field.id)}
        >
          <div className="flex flex-col gap-0.5 mr-1 opacity-30">
            <button
              onClick={(e) => { e.stopPropagation(); moveField(field.id, 'up'); }}
              disabled={idx === 0}
              className="p-0.5 hover:text-gray-700 disabled:opacity-20 transition"
              aria-label="Pindahkan ke atas"
            >
              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 15l7-7 7 7" />
              </svg>
            </button>
            <button
              onClick={(e) => { e.stopPropagation(); moveField(field.id, 'down'); }}
              disabled={idx === sortedFields.length - 1}
              className="p-0.5 hover:text-gray-700 disabled:opacity-20 transition"
              aria-label="Pindahkan ke bawah"
            >
              <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
          <span className={`text-xs font-medium px-2 py-0.5 rounded border ${TYPE_COLORS[field.type] || 'bg-gray-100 text-gray-600 border-gray-200'}`}>
            {typeInfo?.icon || '?'} {typeInfo?.label || field.type}
          </span>
          <div className="flex-1" />
          <button
            onClick={(e) => { e.stopPropagation(); setDeleteFieldId(field.id); }}
            className="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
            title="Hapus field" aria-label="Hapus field"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>

        {/* Card Body - preview area */}
        <div
          className="px-4 pt-3 pb-2 cursor-pointer"
          onClick={() => setSelectedFieldId(isSelected ? null : field.id)}
        >
          <div className="flex items-start gap-2">
            <div className="flex-1 min-w-0">
              {['heading', 'paragraph'].includes(field.type) ? (
                renderFieldPreview(field)
              ) : (
                <>
                  <div className="mb-2">
                    <input
                      type="text"
                      value={field.label}
                      onChange={(e) => updateField({ ...field, label: e.target.value })}
                      onClick={(e) => e.stopPropagation()}
                      placeholder="Teks pertanyaan"
                      className="w-full text-sm font-medium text-gray-800 bg-transparent border-none p-0 focus:outline-none focus:ring-0 placeholder-gray-400"
                    />
                  </div>
                  {renderFieldPreview(field)}
                </>
              )}
            </div>
          </div>
          {!['heading', 'paragraph'].includes(field.type) && (
            <div className="flex items-center justify-end mt-2 pt-2 border-t border-gray-100">
              <label
                className="flex items-center gap-2 cursor-pointer text-sm text-gray-500"
                onClick={(e) => e.stopPropagation()}
              >
                <span>Wajib</span>
                <input
                  type="checkbox"
                  checked={field.required}
                  onChange={(e) => updateField({ ...field, required: e.target.checked })}
                  className="w-3.5 h-3.5 rounded accent-kbb-700"
                />
              </label>
            </div>
          )}
        </div>

        {/* Expandable Settings */}
        {isSelected && (
          <div className="border-t border-gray-100 bg-gray-50/50 px-4 py-3 animate-fade-in">
            <div className="flex items-center gap-2 mb-3">
              <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Properti</span>
            </div>
            {renderFieldSettings(field)}
          </div>
        )}
      </div>
    );
  };

  if (loading) return <LoadingSpinner />;
  if (!form) return <div className="text-center py-12 text-gray-500">Form tidak ditemukan</div>;

  return (
    <div className="space-y-6 animate-fade-in-up max-w-4xl mx-auto">
      {/* Breadcrumb */}
      <nav className="flex items-center gap-1.5 text-xs text-gray-400">
        <Link href="/forms" className="hover:text-kbb-700 transition">Formulir</Link>
        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
        </svg>
        <span className="text-gray-500 truncate max-w-[200px]">{form.title || 'Form Baru'}</span>
        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
        </svg>
        <span className="text-gray-700 font-medium">Edit</span>
      </nav>

      {/* Top Header Card */}
      <div className="kbb-card p-6">
        <div className="flex items-start justify-between gap-4 mb-4">
          <div className="flex-1 min-w-0">
            <input
              type="text"
              value={form.title}
              onChange={(e) => updateForm({ title: e.target.value })}
              placeholder="Judul formulir"
              className="w-full text-2xl font-bold text-gray-900 bg-transparent border-none p-0 focus:outline-none focus:ring-0 placeholder-gray-300"
            />
            <textarea
              value={form.description || ''}
              onChange={(e) => updateForm({ description: e.target.value })}
              placeholder="Deskripsi formulir (opsional)"
              rows={2}
              className="w-full mt-1 text-sm text-gray-500 bg-transparent border-none p-0 focus:outline-none focus:ring-0 placeholder-gray-300 resize-none"
            />
          </div>
          <div className="flex-shrink-0">{statusBadge(form.status)}</div>
        </div>
        <div className="flex items-center justify-between pt-3 border-t border-gray-100">
          <Link
            href={`/forms/${form.id}`}
            className="kbb-btn kbb-btn-ghost kbb-btn-sm"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </Link>
          <div className="flex items-center gap-2">
            {form.status === 'draft' && (
              <button onClick={handlePublish} className="kbb-btn kbb-btn-accent kbb-btn-sm">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                </svg>
                Publikasikan
              </button>
            )}
            {form.status === 'published' && (
              <button onClick={handleClose} className="kbb-btn kbb-btn-sm text-white bg-red-600 hover:bg-red-500 border-red-600">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Tutup
              </button>
            )}
            <button onClick={handleSave} disabled={saving} className="kbb-btn kbb-btn-primary kbb-btn-sm">
              {saving ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        </div>
      </div>

      {/* Flash messages */}
      {error && <div role="alert" className="kbb-alert kbb-alert-danger">{error}</div>}
      {success && <div role="status" className="kbb-alert kbb-alert-success">{success}</div>}

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <div className="flex gap-0">
          <button
            onClick={() => setActiveTab('fields')}
            className={`px-5 py-3 text-sm font-medium transition border-b-2 -mb-px ${
              activeTab === 'fields'
                ? 'text-kbb-700 border-kbb-700'
                : 'text-gray-500 hover:text-gray-700 border-transparent'
            }`}
          >
            Pertanyaan
          </button>
          <button
            onClick={() => setActiveTab('settings')}
            className={`px-5 py-3 text-sm font-medium transition border-b-2 -mb-px ${
              activeTab === 'settings'
                ? 'text-kbb-700 border-kbb-700'
                : 'text-gray-500 hover:text-gray-700 border-transparent'
            }`}
          >
            Setelan
          </button>
        </div>
      </div>

      {/* ── Fields Tab ── */}
      {activeTab === 'fields' && (
        <div className="space-y-3">
          {sortedFields.length === 0 ? (
            <div className="kbb-card p-10 text-center">
              <div className="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <svg className="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </div>
              <h3 className="text-base font-semibold text-gray-700 mb-1">Belum ada pertanyaan</h3>
              <p className="text-sm text-gray-400 mb-6">Klik tombol di bawah untuk menambahkan pertanyaan pertama</p>
              <button
                onClick={() => setShowFieldPicker(true)}
                className="kbb-btn kbb-btn-primary"
              >
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pertanyaan
              </button>
            </div>
          ) : (
            <>
              {sortedFields.map((field: any, idx: number) => renderFieldCard(field, idx))}

              {/* Add Field Button */}
              <div className="relative flex justify-center pt-2" ref={pickerRef}>
                <button
                  onClick={() => setShowFieldPicker(!showFieldPicker)}
                  className="kbb-btn kbb-btn-primary"
                >
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Tambah Pertanyaan
                </button>

                {/* Field Type Picker */}
                {showFieldPicker && (
                  <div className="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 w-[560px] max-w-[90vw] bg-white rounded-xl shadow-xl border border-gray-200 z-30 animate-scale-in">
                    <div className="p-3">
                      <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1">Pilih tipe pertanyaan</p>
                      <div className="grid grid-cols-4 gap-1.5">
                        {FIELD_TYPES.map((t) => (
                          <button
                            key={t.value}
                            onClick={() => addField(t.value)}
                            className={`flex flex-col items-center gap-1 px-2 py-3 rounded-lg text-xs font-medium transition border ${
                              TYPE_COLORS[t.value]
                            } hover:shadow-sm`}
                          >
                            <span className="text-base font-bold">{t.icon}</span>
                            <span className="text-center leading-tight">{t.label}</span>
                          </button>
                        ))}
                      </div>
                    </div>
                    <div className="border-t border-gray-100 px-3 py-2 flex justify-end">
                      <button
                        onClick={() => setShowFieldPicker(false)}
                        className="text-xs text-gray-500 hover:text-gray-700 font-medium"
                      >
                        Tutup
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      )}

      {/* ── Settings Tab ── */}
      {activeTab === 'settings' && (
        <div className="space-y-5">
          {/* Informasi Umum */}
          <div className="kbb-card">
            <div className="kbb-card-header">
              <svg className="w-4 h-4 text-kbb-700 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Informasi Umum
            </div>
            <div className="kbb-card-body space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="kbb-label">Judul Formulir</label>
                  <input
                    type="text"
                    value={form.title}
                    onChange={(e) => updateForm({ title: e.target.value })}
                    placeholder="Judul formulir"
                    className="kbb-input"
                  />
                </div>
                <div>
                  <label className="kbb-label">Deskripsi</label>
                  <input
                    type="text"
                    value={form.description || ''}
                    onChange={(e) => updateForm({ description: e.target.value })}
                    placeholder="Deskripsi singkat"
                    className="kbb-input"
                  />
                </div>
              </div>
            </div>
          </div>

          {/* Jadwal & Batasan */}
          <div className="kbb-card">
            <div className="kbb-card-header">
              <svg className="w-4 h-4 text-kbb-700 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Jadwal & Batasan
            </div>
            <div className="kbb-card-body space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="kbb-label">Tanggal Mulai</label>
                  <input
                    type="date"
                    value={form.starts_at ? form.starts_at.substring(0, 10) : ''}
                    onChange={(e) => updateForm({ starts_at: e.target.value || null })}
                    className="kbb-input"
                  />
                </div>
                <div>
                  <label className="kbb-label">Tanggal Selesai</label>
                  <input
                    type="date"
                    value={form.ends_at ? form.ends_at.substring(0, 10) : ''}
                    onChange={(e) => updateForm({ ends_at: e.target.value || null })}
                    className="kbb-input"
                  />
                </div>
                <div>
                  <label className="kbb-label">Maks. Pengiriman</label>
                  <input
                    type="number"
                    value={form.max_submissions ?? ''}
                    onChange={(e) => updateForm({ max_submissions: e.target.value ? Number(e.target.value) : null })}
                    placeholder="Tak terbatas"
                    className="kbb-input"
                  />
                </div>
              </div>
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.limit_one_response ?? false}
                  onChange={(e) => updateForm({ limit_one_response: e.target.checked })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Batasi 1 respon per pengguna</span>
              </label>
            </div>
          </div>

          {/* Privasi & Pengumpulan Data */}
          <div className="kbb-card">
            <div className="kbb-card-header">
              <svg className="w-4 h-4 text-kbb-700 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Privasi & Pengumpulan Data
            </div>
            <div className="kbb-card-body space-y-3">
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.require_auth ?? false}
                  onChange={(e) => updateForm({ require_auth: e.target.checked })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Pengguna harus login</span>
              </label>
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.settings?.require_email ?? false}
                  onChange={(e) => updateForm({
                    settings: { ...(form.settings || {}), require_email: e.target.checked }
                  })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Kumpulkan email pengirim</span>
              </label>
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.collect_ip ?? false}
                  onChange={(e) => updateForm({ collect_ip: e.target.checked })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Kumpulkan alamat IP</span>
              </label>
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.allow_anonymous ?? false}
                  onChange={(e) => updateForm({ allow_anonymous: e.target.checked })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Izinkan pengiriman anonim</span>
              </label>
            </div>
          </div>

          {/* Tampilan & Branding */}
          <div className="kbb-card">
            <div className="kbb-card-header">
              <svg className="w-4 h-4 text-kbb-700 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
              Tampilan & Branding
            </div>
            <div className="kbb-card-body space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="kbb-label">Teks Tombol Kirim</label>
                  <input
                    type="text"
                    value={form.submit_button_text || 'Kirim'}
                    onChange={(e) => updateForm({ submit_button_text: e.target.value || null })}
                    className="kbb-input"
                  />
                </div>
                <div>
                  <label className="kbb-label">Pesan Sambutan</label>
                  <input
                    type="text"
                    value={form.welcome_message || ''}
                    onChange={(e) => updateForm({ welcome_message: e.target.value || null })}
                    placeholder="Selamat datang"
                    className="kbb-input"
                  />
                </div>
              </div>
              <label className="flex items-center gap-2.5 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.show_kbb_logo !== false}
                  onChange={(e) => updateForm({ show_kbb_logo: e.target.checked })}
                  className="w-4 h-4 rounded accent-kbb-700"
                />
                <span className="text-sm text-gray-600">Tampilkan logo KBB di formulir publik</span>
              </label>
            </div>
          </div>

          {/* Konfirmasi */}
          <div className="kbb-card">
            <div className="kbb-card-header">
              <svg className="w-4 h-4 text-kbb-700 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Konfirmasi
            </div>
            <div className="kbb-card-body space-y-4">
              <div>
                <label className="kbb-label">Tipe Konfirmasi</label>
                <select
                  value={form.confirmation_type || 'message'}
                  onChange={(e) => updateForm({ confirmation_type: e.target.value })}
                  className="kbb-input"
                >
                  <option value="message">Pesan</option>
                  <option value="redirect">Redirect URL</option>
                </select>
              </div>
              {form.confirmation_type === 'redirect' ? (
                <div>
                  <label className="kbb-label">URL Redirect</label>
                  <input
                    type="url"
                    value={form.redirect_url || ''}
                    onChange={(e) => updateForm({ redirect_url: e.target.value || null })}
                    placeholder="https://example.com/thank-you"
                    className="kbb-input"
                  />
                </div>
              ) : (
                <div>
                  <label className="kbb-label">Pesan Konfirmasi</label>
                  <textarea
                    value={form.confirmation_message || ''}
                    onChange={(e) => updateForm({ confirmation_message: e.target.value || null })}
                    rows={2}
                    placeholder="Terima kasih telah mengisi formulir ini."
                    className="kbb-input"
                  />
                </div>
              )}
            </div>
          </div>


        </div>
      )}

      {/* Confirm Delete Dialog */}
      <ConfirmDialog
        open={!!deleteFieldId}
        title="Hapus Field"
        message="Apakah Anda yakin ingin menghapus field ini?"
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        onConfirm={deleteField}
        onCancel={() => setDeleteFieldId(null)}
      />
    </div>
  );
}
