import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { formService } from '../../services';
import StatusBadge from '../../components/StatusBadge';
import ConfirmDialog from '../../components/ConfirmDialog';

interface Props {
  form: any;
}

export default function FormShow({ form }: Props) {
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [copied, setCopied] = useState(false);

  const handlePublish = async () => {
    await formService.publish(form.id);
    router.visit(`/forms/${form.id}/edit`);
  };

  const handleClose = async () => {
    await formService.close(form.id);
    router.reload();
  };

  const handleDuplicate = async () => {
    const res = await formService.duplicate(form.id);
    router.visit(`/forms/${res.id}/edit`);
  };

  const handleDelete = async () => {
    await formService.delete(form.id);
    router.visit('/forms');
  };

  const handleCopyLink = () => {
    const publicUrl = `${window.location.origin}/form/${form.slug}`;
    navigator.clipboard.writeText(publicUrl);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const publicUrl = `${window.location.origin}/form/${form.slug}`;

  return (
    <div className="space-y-6">
      <nav className="flex items-center gap-2 text-sm text-gray-500">
        <Link href="/forms" className="hover:text-[#003778] transition">Formulir</Link>
        <span>/</span>
        <span className="text-gray-900 font-medium">{form.title}</span>
      </nav>

      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex items-center gap-4">
          <div className="w-12 h-12 rounded-xl bg-blue-50 border border-gray-200 flex items-center justify-center flex-shrink-0">
            <svg className="w-6 h-6 text-[#003778]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          </div>
          <div>
            <div className="flex items-center gap-3">
              <h1 className="text-2xl font-bold text-gray-900">{form.title}</h1>
              <StatusBadge status={form.status} />
            </div>
            {form.description && <p className="text-sm text-gray-500 mt-1">{form.description}</p>}
          </div>
        </div>
        <div className="flex flex-wrap gap-2">
          {form.status === 'draft' ? (
            <button onClick={handlePublish} className="px-4 py-2 rounded-lg bg-[#C8A45C] hover:bg-[#b8943d] text-white text-sm font-semibold shadow-sm transition">
              Publikasikan
            </button>
          ) : form.status === 'published' ? (
            <button onClick={handleClose} className="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-semibold shadow-sm transition">
              Tutup
            </button>
          ) : null}
          <Link href={`/forms/${form.id}/edit`} className="kbb-btn kbb-btn-primary">Edit</Link>
        </div>
      </div>

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div className="kbb-card p-4">
          <p className="kbb-text-muted text-xs uppercase tracking-wider font-medium">Fields</p>
          <p className="text-lg font-bold mt-1">{form.fields_count ?? form.fields?.length ?? 0}</p>
        </div>
        <div className="kbb-card p-4">
          <p className="kbb-text-muted text-xs uppercase tracking-wider font-medium">Responses</p>
          <p className="text-lg font-bold mt-1">{form.submissions_count ?? 0}</p>
        </div>
        <div className="kbb-card p-4">
          <p className="kbb-text-muted text-xs uppercase tracking-wider font-medium">Status</p>
          <p className="text-lg font-bold mt-1 capitalize">{form.status}</p>
        </div>
        <div className="kbb-card p-4">
          <p className="kbb-text-muted text-xs uppercase tracking-wider font-medium">Link Publik</p>
          <button onClick={handleCopyLink} className="kbb-link text-sm mt-1 truncate block w-full text-left">
            {copied ? 'Tersalin!' : publicUrl.length > 30 ? publicUrl.substring(0, 30) + '...' : publicUrl}
          </button>
        </div>
      </div>

      {form.status === 'published' && (
        <div className="kbb-card p-5">
          <p className="kbb-text-muted text-sm mb-2">Link Publik</p>
          <div className="flex gap-2">
            <input type="text" value={publicUrl} readOnly onClick={(e) => (e.target as HTMLInputElement).select()}
              className="kbb-input flex-1" />
            <button onClick={handleCopyLink} className="kbb-btn kbb-btn-primary">{copied ? 'Tersalin' : 'Salin'}</button>
          </div>
        </div>
      )}

      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <Link href={`/forms/${form.id}/submissions`} className="kbb-card p-4 text-center hover:border-gray-300 transition group">
          <svg className="w-6 h-6 mx-auto mb-2 text-gray-400 group-hover:kbb-text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
          <p className="kbb-text-muted text-xs mt-1">Lihat Data</p>
        </Link>
        <Link href={`/forms/${form.id}/analytics`} className="kbb-card p-4 text-center hover:border-gray-300 transition group">
          <svg className="w-6 h-6 mx-auto mb-2 text-gray-400 group-hover:kbb-text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
          <p className="kbb-text-muted text-xs mt-1">Analytics</p>
        </Link>
        <button onClick={handleDuplicate} className="kbb-card p-4 text-center hover:border-gray-300 transition group">
          <svg className="w-6 h-6 mx-auto mb-2 text-gray-400 group-hover:kbb-text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
          <p className="kbb-text-muted text-xs mt-1">Duplikat</p>
        </button>
        <button onClick={() => setShowDeleteDialog(true)} className="kbb-card p-4 text-center hover:border-red-300 transition group">
          <svg className="w-6 h-6 mx-auto mb-2 text-gray-400 group-hover:text-red-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          <p className="kbb-text-muted text-xs mt-1">Hapus</p>
        </button>
      </div>

      {form.fields?.length > 0 && (
        <div className="kbb-card p-5">
          <h2 className="text-sm font-semibold mb-4">Daftar Field</h2>
          <div className="space-y-2">
            {[...form.fields].sort((a: any, b: any) => a.order - b.order).map((field: any, i: number) => (
              <div key={field.id} className="flex items-center gap-3 px-4 py-2.5 rounded-lg bg-gray-50 border border-gray-100">
                <span className="text-xs text-gray-400 font-mono w-6">{i + 1}.</span>
                <span className="text-xs text-gray-500 uppercase tracking-wider w-20">{field.type}</span>
                <span className="text-sm flex-1">{field.label}</span>
                {field.required && <span className="text-xs text-red-500">*</span>}
              </div>
            ))}
          </div>
        </div>
      )}

      <ConfirmDialog open={showDeleteDialog} title="Hapus Form" message="Apakah Anda yakin ingin menghapus form ini? Semua data submissions akan ikut terhapus." confirmLabel="Hapus" cancelLabel="Batal" variant="danger" onConfirm={handleDelete} onCancel={() => setShowDeleteDialog(false)} />
    </div>
  );
}
