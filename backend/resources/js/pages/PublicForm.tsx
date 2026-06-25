import { useState, useEffect } from 'react';
import { formService } from '../services';
import LoadingSpinner from '../components/LoadingSpinner';

interface Props { slug: string }

export default function PublicForm({ slug }: Props) {
  const [form, setForm] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');
  const [values, setValues] = useState<Record<string, string>>({});

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.getPublicForm(slug);
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

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form) return;
    setError('');
    setSubmitting(true);
    try {
      const payload: { fields: Record<number, any> } = { fields: {} };
      form.fields.forEach((field: any) => {
        const key = field.id.toString();
        if (values[key] !== undefined && values[key] !== '') {
          payload.fields[field.id] = values[key];
        }
      });
      await formService.submitPublicForm(form.slug || slug, payload);

      if (form.confirmation_type === 'redirect' && form.redirect_url) {
        window.location.href = form.redirect_url;
        return;
      }
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
      <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div className="kbb-card p-8 max-w-md w-full text-center animate-fade-in-up">
          <div className="w-14 h-14 mx-auto mb-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 flex items-center justify-center">
            <svg className="h-7 w-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
          </div>
          <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-1">Formulir Tidak Ditemukan</h2>
          <p className="text-sm text-gray-500 dark:text-gray-400">{error || 'Formulir ini tidak tersedia atau sudah ditutup.'}</p>
        </div>
      </div>
    );
  }

  const sortedFields = [...form.fields].sort((a: any, b: any) => a.order - b.order);
  const requiredFields = sortedFields.filter((f: any) => f.required);
  const filledRequired = requiredFields.filter((f: any) => {
    const val = values[f.id.toString()];
    if (f.type === 'checkbox') return val && val.split(',').filter(Boolean).length > 0;
    return !!val?.trim();
  });
  const progress = requiredFields.length > 0 ? (filledRequired.length / requiredFields.length) * 100 : 0;

  if (submitted) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
        <div className="kbb-card p-8 max-w-md w-full text-center animate-fade-in-up">
          <div className="w-16 h-16 mx-auto mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center">
            <svg className="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
          <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">Respons Tersimpan</h2>
          <p className="text-gray-500 dark:text-gray-400">{form.confirmation_message || 'Terima kasih telah mengisi formulir ini.'}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-8 px-4 flex items-center justify-center bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800">
      <div className="max-w-2xl w-full">
        <div className="text-center mb-6 animate-fade-in-up">
          {(form.show_kbb_logo ?? true) && (
            <img src="/images/kbb-logo.png" alt="KBB" className="h-16 mx-auto mb-3" />
          )}
          <h1 className="text-xl font-bold text-gray-900 dark:text-white">{form.title}</h1>
          {form.description && <p className="text-gray-500 dark:text-gray-400 text-sm mt-1">{form.description}</p>}
        </div>

        <form onSubmit={handleSubmit} className="kbb-card p-6 sm:p-8 space-y-5 animate-fade-in-up">
          <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#003778] via-[#C8A45C] to-[#003778] rounded-t-2xl" style={{ margin: 0, padding: 0 }} />

          {error && <div role="alert" className="flex items-center gap-2.5 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-600 dark:text-red-400 text-sm">{error}</div>}

          {requiredFields.length > 0 && (
            <div>
              <div className="flex justify-between text-gray-500 dark:text-gray-400 text-xs mb-1.5">
                <span>Progress</span>
                <span>{filledRequired.length}/{requiredFields.length}</span>
              </div>
              <div className="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div className="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-[#003778] to-blue-600" style={{ width: `${progress}%` }} />
              </div>
            </div>
          )}

          {sortedFields.map((field: any) => (
            <div key={field.id}>
              {field.type === 'heading' && (
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white pt-2">{field.label}</h3>
              )}
              {field.type === 'paragraph' && (
                <p className="text-sm text-gray-500 dark:text-gray-400 pt-1">{field.label}</p>
              )}
              {!['heading', 'paragraph'].includes(field.type) && (
                <>
                  <label className="block text-sm text-gray-700 dark:text-gray-300 mb-1.5">{field.label}{field.required && <span className="text-red-500 ml-1">*</span>}</label>
                  {field.type === 'textarea' ? (
                    <textarea rows={3} value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} placeholder={field.placeholder || ''} required={field.required} className="kbb-input resize-none" />
                  ) : field.type === 'select' ? (
                    <select value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} required={field.required} className="kbb-input">
                      <option value="">{field.placeholder || 'Pilih...'}</option>
                      {field.options?.map((opt: any) => <option key={opt} value={opt}>{opt}</option>)}
                    </select>
                  ) : field.type === 'radio' ? (
                    <div className="space-y-1.5">
                      {field.options?.map((opt: any) => (
                        <label key={opt} className="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                          <input type="radio" name={`f_${field.id}`} value={opt} checked={values[field.id.toString()] === opt} onChange={(e) => handleValue(field.id, e.target.value)} required={field.required} className="w-4 h-4 accent-[#003778]" />
                          <span className="text-sm text-gray-700 dark:text-gray-300">{opt}</span>
                        </label>
                      ))}
                    </div>
                  ) : field.type === 'checkbox' ? (
                    <div className="space-y-1.5">
                      {field.options?.map((opt: any) => (
                        <label key={opt} className="flex items-center gap-2.5 cursor-pointer p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                          <input type="checkbox" value={opt} checked={(values[field.id.toString()] || '').split(',').includes(opt)} onChange={(e) => handleCheckbox(field.id, opt, e.target.checked)} className="w-4 h-4 accent-[#003778] rounded" />
                          <span className="text-sm text-gray-700 dark:text-gray-300">{opt}</span>
                        </label>
                      ))}
                    </div>
                  ) : field.type === 'file' ? (
                    <div className="px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 border-dashed text-gray-400 text-sm text-center">
                      Upload file
                    </div>
                  ) : field.type === 'signature' ? (
                    <div className="px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 border-dashed text-gray-400 text-sm text-center">
                      Tanda tangan digital
                    </div>
                  ) : (
                    <input type={field.type === 'date' || field.type === 'time' || field.type === 'number' || field.type === 'email' ? field.type : 'text'}
                      value={values[field.id.toString()] || ''} onChange={(e) => handleValue(field.id, e.target.value)} placeholder={field.placeholder || ''} required={field.required} className="kbb-input" />
                  )}
                </>
              )}
            </div>
          ))}

          <button type="submit" disabled={submitting} className="kbb-btn kbb-btn-primary w-full justify-center">
            {submitting && <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" /><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>}
            {submitting ? 'Mengirim...' : (form.submit_button_text || 'Kirim')}
          </button>
        </form>
        <p className="text-gray-400 dark:text-gray-500 text-xs text-center mt-6">Pemerintah Kabupaten Bandung Barat</p>
      </div>
    </div>
  );
}
