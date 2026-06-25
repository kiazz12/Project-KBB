import { useState, useEffect } from 'react';
import { Link } from '@inertiajs/react';
import { formService } from '../../../services';
import LoadingSpinner from '../../../components/LoadingSpinner';

export default function SubmissionShow({ formId, submissionId }: { formId: number; submissionId: number }) {
  const [submission, setSubmission] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.getSubmission(formId, submissionId);
        setSubmission(res);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [formId, submissionId]);

  if (loading) return <LoadingSpinner />;
  if (!submission) return <div className="text-center py-12 text-gray-500">Data tidak ditemukan</div>;

  return (
    <div className="space-y-6">
      <nav className="flex items-center gap-2 text-sm text-gray-500">
        <Link href={`/forms/${formId}`} className="hover:text-[#003778] transition">Forms</Link>
        <span>/</span>
        <Link href={`/forms/${formId}/submissions`} className="hover:text-[#003778] transition">Data</Link>
        <span>/</span>
        <span className="text-gray-900 font-medium">Detail</span>
      </nav>

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-blue-50 border border-gray-200 flex items-center justify-center flex-shrink-0">
            <svg className="h-6 w-6 text-[#003778]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
          </div>
          <div>
            <h1 className="text-xl font-bold text-gray-900">Detail Pengiriman</h1>
            <p className="text-sm text-gray-500">{submission.uuid?.slice(0, 12)}...</p>
          </div>
        </div>
        <Link href={`/forms/${formId}/submissions`} className="px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 border border-gray-200 transition flex items-center gap-1.5">
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Kembali
        </Link>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <p className="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">UUID</p>
          <p className="text-sm font-mono text-gray-700">{submission.uuid?.slice(0, 12)}...</p>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <p className="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">Dikirim</p>
          <p className="text-sm text-gray-700">{new Date(submission.submitted_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <p className="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">IP</p>
          <p className="text-sm text-gray-700 font-mono">{submission.ip_address ?? '-'}</p>
        </div>
        <div className="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <p className="text-xs text-gray-500 uppercase tracking-wider font-medium mb-1">User Agent</p>
          <p className="text-xs text-gray-400 truncate">{submission.user_agent ?? '-'}</p>
        </div>
      </div>

      <div className="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <h2 className="text-sm font-semibold text-gray-900 pb-4 border-b border-gray-200">Respons</h2>
        {!submission.data?.length ? (
          <p className="text-gray-400 text-sm py-6 text-center">Tidak ada data respons</p>
        ) : (
          <div className="divide-y divide-gray-100">
            {submission.data.map((d: any) => (
              <div key={d.form_field_id || d.id} className="py-4 first:pt-4">
                <p className="text-xs text-gray-500 mb-1.5 font-medium">{d.formField?.label ?? 'Field #' + d.form_field_id}</p>
                {d.formField?.type === 'checkbox' && d.value ? (
                  <div className="flex flex-wrap gap-1.5">
                    {d.value.split(',').map((v: any) => (
                      <span key={v} className="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 border border-gray-200 text-xs text-gray-600">{v}</span>
                    ))}
                  </div>
                ) : d.value ? (
                  <p className="text-sm text-gray-700">{d.value}</p>
                ) : (
                  <p className="text-sm text-gray-400 italic">Tidak ada respons</p>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
