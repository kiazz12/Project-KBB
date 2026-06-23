import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeftIcon, ClockIcon, GlobeAltIcon, FingerPrintIcon, HashtagIcon } from '@heroicons/react/24/outline';
import { formService } from '../../../services';
import LoadingSpinner from '../../../components/LoadingSpinner';

export default function SubmissionShow() {
  const { formId, id } = useParams<{ formId: string; id: string }>();
  const navigate = useNavigate();
  const [submission, setSubmission] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      try {
        const res = await formService.getSubmission(Number(formId), Number(id));
        setSubmission(res);
      } catch { /* ignore */ } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [formId, id]);

  if (loading) return <LoadingSpinner />;
  if (!submission) return <div className="text-center py-12 text-white/50">Data tidak ditemukan</div>;

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex items-center gap-2 text-xs text-white/30 mb-2">
        <button onClick={() => navigate(`/forms/${formId}`)} className="hover:text-gold-400 transition">Forms</button>
        <span>/</span>
        <button onClick={() => navigate(`/forms/${formId}/submissions`)} className="hover:text-gold-400 transition">Data</button>
        <span>/</span>
        <span className="text-white/50">Detail</span>
      </div>

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
            <HashtagIcon className="h-6 w-6 text-gold-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-white">Detail Pengiriman</h1>
            <p className="text-sm text-white/40">{submission.uuid?.slice(0, 12)}...</p>
          </div>
        </div>
        <button onClick={() => navigate(`/forms/${formId}/submissions`)} className="px-3 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 border border-white/10 transition flex items-center gap-1.5">
          <ArrowLeftIcon className="h-4 w-4" /> Kembali
        </button>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
          <div className="flex items-center gap-2 mb-1">
            <HashtagIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">UUID</span>
          </div>
          <p className="text-sm font-mono text-white mt-1">{submission.uuid?.slice(0, 12)}...</p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
          <div className="flex items-center gap-2 mb-1">
            <ClockIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">Dikirim</span>
          </div>
          <p className="text-sm text-white mt-1">
            {new Date(submission.submitted_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
          </p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
          <div className="flex items-center gap-2 mb-1">
            <GlobeAltIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">IP</span>
          </div>
          <p className="text-sm text-white mt-1 font-mono">{submission.ip_address ?? '-'}</p>
        </div>
        <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4">
          <div className="flex items-center gap-2 mb-1">
            <FingerPrintIcon className="h-4 w-4 text-white/30" />
            <span className="text-xs text-white/40 uppercase tracking-wider font-medium">User Agent</span>
          </div>
          <p className="text-xs text-white/40 mt-1 truncate">{submission.user_agent ?? '-'}</p>
        </div>
      </div>

      <div className="bg-kbb-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
        <h2 className="text-sm font-semibold text-white pb-4 border-b border-white/10">Respons</h2>
        {submission.data?.length === 0 ? (
          <p className="text-white/40 text-sm py-6 text-center">Tidak ada data respons</p>
        ) : (
          <div className="divide-y divide-white/5">
            {submission.data?.map((d: any) => (
              <div key={d.form_field_id} className="py-4 first:pt-4">
                <p className="text-xs text-white/40 mb-1.5 font-medium">{d.formField?.label ?? 'Field #' + d.form_field_id}</p>
                {['checkbox'].includes(d.formField?.type ?? '') && d.value ? (
                  <div className="flex flex-wrap gap-1.5">
                    {d.value.split(',').map((v: any) => (
                      <span key={v} className="inline-flex items-center px-2 py-0.5 rounded-lg bg-white/5 border border-white/10 text-xs text-white/50">{v}</span>
                    ))}
                  </div>
                ) : d.value ? (
                  <p className="text-sm text-white">{d.value}</p>
                ) : (
                  <p className="text-sm text-white/30 italic">Tidak ada respons</p>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
