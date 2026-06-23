import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';
import { formService } from '../services';
import LoadingSpinner from '../components/LoadingSpinner';

export default function PublicForm() {
  const { slug } = useParams<{ slug: string }>();
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');
  const [values, setValues] = useState<Record<string, string>>({});
  const [fileValues, setFileValues] = useState<Record<string, File | null>>({});

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.getPublicForm(slug!);
        setForm(res);
      } catch {
        setError('Formulir tidak ditemukan atau sudah ditutup.');
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [slug]);

  const handleValue = (fieldId: number, value: string) => setValues((p) => ({ ...p, [fieldId.toString()]: value }));
  const handleCheckbox = (fieldId: number, option: string, checked: boolean) => {
    const key = fieldId.toString();
    const current = (values[key] || '').split(',').filter(Boolean);
    const updated = checked ? [...current, option] : current.filter((v) => v !== option);
    setValues((p) => ({ ...p, [key]: updated.join(',') }));
  };
  const handleFile = (fieldId: number, file: File | null) => setFileValues((p) => ({ ...p, [fieldId.toString()]: file }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form) return;
    setError('');
    setSubmitting(true);
    try {
      const formData = new FormData();
      form.fields.forEach((field: any) => {
        const key = field.id.toString();
        if (field.type === 'file') {
          if (fileValues[key]) formData.append(`fields[${field.id}]`, fileValues[key]!);
        } else if (values[key]) {
          formData.append(`fields[${field.id}]`, values[key]);
        }
      });
      await formService.submitPublicForm(form.slug || slug!, formData as any);
      setSubmitted(true);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Gagal mengirim formulir.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <LoadingSpinner />;

  if (!form) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4 bg-[#0a0a1a]">
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-md w-full text-center">
          <div className="w-14 h-14 mx-auto mb-4 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
            <ExclamationTriangleIcon className="h-7 w-7 text-red-400" />
          </div>
          <h2 className="text-lg font-semibold text-white mb-1">Formulir Tidak Ditemukan</h2>
          <p className="text-sm text-white/40">{error || 'Formulir ini tidak tersedia.'}</p>
        </div>
      </div>
    );
  }

  if (submitted) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4 bg-[#0a0a1a]">
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 max-w-md w-full text-center animate-fade-in-up">
          <div className="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
            <CheckCircleIcon className="h-8 w-8 text-emerald-400" />
          </div>
          <h2 className="text-xl font-semibold text-white mb-2">Respons Tersimpan</h2>
          <p className="text-white/50">{form.confirmation_message || 'Terima kasih telah mengisi formulir ini.'}</p>
        </div>
      </div>
    );
  }

  const sortedFields = [...form.fields].sort((a: any, b: any) => a.order - b.order);
  const requiredFields = sortedFields.filter((f: any) => f.required);
  const filledRequired = requiredFields.filter((f: any) => {
    const val = values[f.id.toString()];
    if (f.type === 'checkbox') return val && val.split(',').filter(Boolean).length > 0;
    if (f.type === 'file') return !!fileValues[f.id.toString()];
    return !!val?.trim();
  });
  const progress = requiredFields.length > 0 ? (filledRequired.length / requiredFields.length) * 100 : 0;

  return (
    <div className="min-h-screen py-8 px-4 flex items-center justify-center bg-[#0a0a1a]">
      <div className="max-w-2xl w-full">
        <div className="text-center mb-6 animate-fade-in-up">
          <div className="w-14 h-14 mx-auto mb-3 rounded-xl bg-gradient-to-br from-kbb-700 to-kbb-950 shadow-lg shadow-kbb-700/30 ring-1 ring-white/10 flex items-center justify-center">
            <img src="/images/kbb-logo.png" alt="KBB" className="w-9 h-9" />
          </div>
          <h1 className="text-xl font-bold text-white">{form.title}</h1>
          {form.description && <p className="text-white/40 text-sm mt-1">{form.description}</p>}
        </div>

        <form onSubmit={handleSubmit} className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 space-y-5 animate-fade-in-up">
          {error && (
            <div className="flex items-center gap-2.5 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">{error}</div>
          )}

          {requiredFields.length > 0 && (
            <div>
              <div className="flex justify-between text-white/40 text-xs mb-1.5">
                <span>Progress</span>
                <span>{filledRequired.length}/{requiredFields.length}</span>
              </div>
              <div className="h-2 bg-white/5 rounded-full overflow-hidden">
                <div className="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-kbb-700 to-kbb-500" style={{ width: `${progress}%` }} />
              </div>
            </div>
          )}

          {sortedFields.map((field: any) => (
            <div key={field.id}>
              <label className="block text-sm text-white/70 mb-1.5">{field.label}{field.required && <span className="text-red-400 ml-1">*</span>}</label>

              {field.type === 'textarea' ? (
                <textarea rows={3} value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} placeholder={field.placeholder || ''} required={field.required}
                  className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition resize-none" />
              ) : field.type === 'select' ? (
                <select value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} required={field.required}
                  className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition">
                  <option value="">{field.placeholder || 'Pilih...'}</option>
                  {field.options?.map((opt: any) => <option key={opt}>{opt}</option>)}
                </select>
              ) : field.type === 'radio' ? (
                <div className="space-y-2">
                  {field.options?.map((opt: any) => (
                    <label key={opt} className="flex items-center gap-2.5 cursor-pointer">
                      <input type="radio" name={`f_${field.id}`} value={opt} checked={values[field.id.toString()] === opt} onChange={(e) => handleValue(field.id, e.target.value)} required={field.required} className="w-4 h-4 accent-gold-400" />
                      <span className="text-sm text-white/70">{opt}</span>
                    </label>
                  ))}
                </div>
              ) : field.type === 'checkbox' ? (
                <div className="space-y-2">
                  {field.options?.map((opt: any) => (
                    <label key={opt} className="flex items-center gap-2.5 cursor-pointer">
                      <input type="checkbox" value={opt} checked={(values[field.id.toString()] || '').split(',').includes(opt)} onChange={(e) => handleCheckbox(field.id, opt, e.target.checked)} className="w-4 h-4 accent-gold-400 rounded" />
                      <span className="text-sm text-white/70">{opt}</span>
                    </label>
                  ))}
                </div>
              ) : field.type === 'file' ? (
                <input type="file" onChange={(e) => handleFile(field.id, e.target.files?.[0] || null)} required={field.required}
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-kbb-700/50 file:text-white/80 hover:file:bg-kbb-700/70" />
              ) : field.type === 'rating' ? (
                <div className="flex gap-2">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <button key={star} type="button" onClick={() => handleValue(field.id, star.toString())}
                      className={`w-10 h-10 rounded-lg text-lg transition-all ${
                        parseInt(values[field.id.toString()] || '0') >= star
                          ? 'bg-gold-400/90 text-white shadow-lg shadow-gold-400/30'
                          : 'bg-white/5 text-white/40 hover:bg-white/10'
                      }`}>{star}</button>
                  ))}
                </div>
              ) : (
                <input type={field.type} value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} placeholder={field.placeholder || ''} required={field.required}
                  className="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/20 focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              )}
            </div>
          ))}

          <button type="submit" disabled={submitting}
            className="w-full py-2.5 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white font-semibold text-sm transition shadow-lg shadow-kbb-700/30 flex items-center justify-center gap-2">
            {submitting && <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>}
            {submitting ? 'Mengirim...' : 'Kirim'}
          </button>
        </form>

        <p className="text-white/20 text-xs text-center mt-6">Pemerintah Kabupaten Bandung Barat</p>
      </div>
    </div>
  );
}
