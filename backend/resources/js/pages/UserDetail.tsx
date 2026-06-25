import { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { userService, formService } from '../services';
import LoadingSpinner from '../components/LoadingSpinner';
import ConfirmDialog from '../components/ConfirmDialog';
import type { User, Form } from '../types';

interface FormsMeta {
  current_page: number;
  last_page: number;
  total: number;
}

interface FormData {
  name: string;
  email: string;
  password: string;
  role: User['role'];
  nip: string;
  opd: string;
}

const initialFormData: FormData = {
  name: '', email: '', password: '', role: 'admin', nip: '', opd: '',
};

function RoleBadge({ role }: { role: string }) {
  const styles: Record<string, string> = {
    super_admin: 'bg-amber-50 text-amber-700 border-amber-200',
    admin: 'bg-blue-50 text-blue-700 border-blue-200',
  };
  return (
    <span className={`text-xs px-2 py-0.5 rounded-lg border font-medium ${styles[role] || ''}`}>
      {role === 'super_admin' ? 'Super Admin' : role.charAt(0).toUpperCase() + role.slice(1)}
    </span>
  );
}

function StatusBadge({ status }: { status: string }) {
  const styles: Record<string, string> = {
    published: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    draft: 'bg-amber-50 text-amber-700 border-amber-200',
    closed: 'bg-gray-50 text-gray-500 border-gray-200',
  };
  const labels: Record<string, string> = {
    published: 'Published',
    draft: 'Draft',
    closed: 'Closed',
  };
  return (
    <span className={`text-xs px-2 py-0.5 rounded-lg border font-medium ${styles[status] || styles.closed}`}>
      {labels[status] || status}
    </span>
  );
}

export default function UserDetail() {
  const { user: userProp } = usePage().props as unknown as { user: User };
  const [user, setUser] = useState<User>(userProp);
  const [userForms, setUserForms] = useState<Form[]>([]);
  const [formsMeta, setFormsMeta] = useState<FormsMeta | null>(null);
  const [formsLoading, setFormsLoading] = useState(false);
  const [formsPage, setFormsPage] = useState(1);

  const [showForm, setShowForm] = useState(false);
  const [editUser, setEditUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<FormData>(initialFormData);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState('');

  const [deleteUserId, setDeleteUserId] = useState<number | null>(null);

  const [resetUser, setResetUser] = useState<User | null>(null);
  const [newPassword, setNewPassword] = useState('');
  const [resetting, setResetting] = useState(false);
  const [resetError, setResetError] = useState('');

  const [deleteFormId, setDeleteFormId] = useState<number | null>(null);
  const [deleteFormTitle, setDeleteFormTitle] = useState('');

  useEffect(() => {
    fetchUserForms(user.id, 1);
  }, []);

  const fetchUserForms = async (userId: number, page = 1) => {
    setFormsLoading(true);
    try {
      const res = await userService.getUserForms(userId, { page, per_page: 10 });
      if (res && Array.isArray(res.data)) {
        setUserForms(res.data);
        setFormsMeta({ current_page: res.current_page, last_page: res.last_page, total: res.total });
      } else {
        setUserForms([]);
        setFormsMeta(null);
      }
    } catch {
      setUserForms([]);
      setFormsMeta(null);
    } finally {
      setFormsLoading(false);
    }
  };

  const reloadUser = () => {
    router.visit(`/users/${user.id}`, { preserveScroll: true });
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setFormError('');
    try {
      if (editUser) {
        const data: any = {
          name: formData.name,
          role: formData.role,
          nip: formData.nip || undefined,
          opd: formData.opd || undefined,
        };
        if (formData.password) {
          data.password = formData.password;
        }
        await userService.updateUser(editUser.id, data);
      } else {
        await userService.createUser({
          name: formData.name,
          email: formData.email,
          password: formData.password,
          role: formData.role,
          nip: formData.nip || undefined,
          opd: formData.opd || undefined,
        });
      }
      setShowForm(false);
      reloadUser();
    } catch (err: any) {
      setFormError(err.response?.data?.message || 'Gagal menyimpan user.');
    } finally {
      setSaving(false);
    }
  };

  const openEdit = () => {
    setEditUser(user);
    setFormData({
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
      nip: user.nip || '',
      opd: user.opd || '',
    });
    setShowForm(true);
    setFormError('');
  };

  const handleDeleteUser = async () => {
    if (!deleteUserId) return;
    try {
      await userService.deleteUser(deleteUserId);
      setDeleteUserId(null);
      router.visit('/users');
    } catch { /* ignore */ }
  };

  const openResetPassword = () => {
    setResetUser(user);
    setNewPassword('');
    setResetError('');
  };

  const handleResetPassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!resetUser || !newPassword) return;
    setResetting(true);
    setResetError('');
    try {
      await userService.updateUser(resetUser.id, { password: newPassword });
      setResetUser(null);
      setNewPassword('');
      reloadUser();
    } catch (err: any) {
      setResetError(err.response?.data?.message || 'Gagal mereset password.');
    } finally {
      setResetting(false);
    }
  };

  const handleDeleteForm = async () => {
    if (!deleteFormId) return;
    try {
      await formService.delete(deleteFormId);
      setUserForms((prev) => prev.filter((f) => f.id !== deleteFormId));
      if (formsMeta) {
        fetchUserForms(user.id, formsMeta.current_page);
      }
      setDeleteFormId(null);
    } catch { /* ignore */ }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-3">
        <Link href="/users" className="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" aria-label="Kembali ke daftar pengguna">
          <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 12H5m7-7l-7 7 7 7" /></svg>
        </Link>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Detail Pengguna</h1>
          <p className="text-sm text-gray-500 mt-1">Informasi akun dan form yang dibuat</p>
        </div>
      </div>

      <div className="kbb-card">
        <div className="p-6">
          <div className="flex items-start gap-4 mb-6">
            <div className="w-14 h-14 rounded-full bg-[#C8A45C]/20 border border-[#C8A45C]/30 flex items-center justify-center text-xl font-bold text-[#8B6F3A] flex-shrink-0">
              {user.name.charAt(0).toUpperCase()}
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2">
                <h2 className="text-lg font-semibold text-gray-900">{user.name}</h2>
                <RoleBadge role={user.role} />
              </div>
              <p className="text-sm text-gray-500">{user.email}</p>
              {(user.nip || user.opd) && (
                <p className="text-xs text-gray-400 mt-0.5">
                  {user.nip ? `NIP. ${user.nip}` : ''}
                  {user.nip && user.opd ? ' · ' : ''}
                  {user.opd || ''}
                </p>
              )}
            </div>
          </div>

          <div className="flex flex-wrap gap-2 mb-6">
            <button onClick={openEdit} className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300 text-sm font-medium transition shadow-sm">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              Edit User
            </button>
            <button onClick={openResetPassword} className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:border-gray-300 text-sm font-medium transition shadow-sm">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
              Reset Password
            </button>
            {user.role !== 'super_admin' && (
              <button onClick={() => setDeleteUserId(user.id)} className="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50 text-sm font-medium transition shadow-sm">
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Hapus User
              </button>
            )}
          </div>

          <div>
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-semibold text-gray-700">
                Form dibuat oleh {user.name}
                {formsMeta !== null && <span className="text-gray-400 font-normal ml-1">({formsMeta.total})</span>}
              </h3>
              <Link href="/forms/create" className="text-xs text-[#003778] hover:text-[#002a5c] font-medium inline-flex items-center gap-1">
                <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
                Buat Form Baru
              </Link>
            </div>

            {formsLoading ? (
              <LoadingSpinner />
            ) : userForms.length === 0 ? (
              <div className="bg-gray-50 rounded-xl p-8 text-center text-sm text-gray-400">User ini belum membuat form apapun.</div>
            ) : (
              <div className="space-y-2">
                {userForms.map((f: any) => (
                  <div key={f.id} className="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-4 py-3 hover:border-gray-300 transition">
                    <div className="min-w-0 flex-1">
                      <p className="text-sm font-medium text-gray-900 truncate">{f.title}</p>
                      <p className="text-xs text-gray-400 mt-0.5">{f.fields_count ?? 0} field &middot; {f.submissions_count ?? 0} respons</p>
                    </div>
                    <div className="flex items-center gap-2 flex-shrink-0">
                      <StatusBadge status={f.status} />
                      <Link href={`/forms/${f.id}/edit`} className="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">Edit</Link>
                      <Link href={`/forms/${f.id}/submissions`} className="px-2 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">Data</Link>
                      <button onClick={() => { setDeleteFormId(f.id); setDeleteFormTitle(f.title); }} className="px-2 py-1.5 rounded-lg text-xs font-medium text-red-500 hover:text-red-700 hover:bg-red-50 transition">Hapus</button>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {formsMeta && formsMeta.last_page > 1 && (
              <div className="flex justify-center gap-2 mt-4">
                {Array.from({ length: formsMeta.last_page }, (_, i) => i + 1).map((p) => (
                  <button key={p} onClick={() => { setFormsPage(p); fetchUserForms(user.id, p); }}
                    className={`w-8 h-8 rounded-lg text-xs font-medium transition ${
                      p === formsMeta.current_page ? 'bg-[#003778] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                    }`}>{p}</button>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>

      {showForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={() => setShowForm(false)}>
          <div className="kbb-modal" onClick={(e) => e.stopPropagation()}>
            <div className="kbb-modal-header">
              <span>Edit User</span>
              <button onClick={() => setShowForm(false)} className="p-1 rounded-lg hover:bg-gray-100 transition" aria-label="Tutup">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <div className="kbb-modal-body">
              {formError && <div role="alert" className="kbb-alert kbb-alert-danger mb-4">{formError}</div>}
              <form id="edit-user-form" onSubmit={handleSave} className="space-y-4">
                <div>
                  <label className="kbb-label">Nama</label>
                  <input type="text" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} placeholder="Nama lengkap" required className="kbb-input" />
                </div>
                <div>
                  <label className="kbb-label">Email</label>
                  <input type="email" value={formData.email} onChange={(e) => setFormData({ ...formData, email: e.target.value })} placeholder="email@contoh.com" disabled className="kbb-input disabled:opacity-60" />
                </div>
                <div>
                  <label className="kbb-label">Password Baru <span className="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span></label>
                  <input type="password" value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })} placeholder="Min. 8 karakter" className="kbb-input" />
                </div>
                <div>
                  <label className="kbb-label">Role</label>
                  <select value={formData.role} onChange={(e) => setFormData({ ...formData, role: e.target.value as User['role'] })} className="kbb-input">
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div>
                  <label className="kbb-label">NIP <span className="text-gray-400 font-normal">(opsional)</span></label>
                  <input type="text" value={formData.nip} onChange={(e) => setFormData({ ...formData, nip: e.target.value })} placeholder="Nomor Induk Pegawai" className="kbb-input" />
                </div>
                <div>
                  <label className="kbb-label">OPD <span className="text-gray-400 font-normal">(opsional)</span></label>
                  <input type="text" value={formData.opd} onChange={(e) => setFormData({ ...formData, opd: e.target.value })} placeholder="Organisasi Perangkat Daerah" className="kbb-input" />
                </div>
              </form>
            </div>
            <div className="kbb-modal-footer">
              <button type="button" onClick={() => setShowForm(false)} className="kbb-btn kbb-btn-ghost">Batal</button>
              <button type="submit" disabled={saving} form="edit-user-form" className="kbb-btn kbb-btn-primary">{saving ? 'Menyimpan...' : 'Simpan Perubahan'}</button>
            </div>
          </div>
        </div>
      )}

      {resetUser && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={() => setResetUser(null)}>
          <div className="kbb-modal" onClick={(e) => e.stopPropagation()}>
            <div className="kbb-modal-header">
              <span>Reset Password</span>
              <button onClick={() => setResetUser(null)} className="p-1 rounded-lg hover:bg-gray-100 transition" aria-label="Tutup">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <div className="kbb-modal-body">
              <p className="text-sm text-gray-500 mb-4">Reset password untuk <strong>{resetUser.name}</strong></p>
              {resetError && <div role="alert" className="kbb-alert kbb-alert-danger mb-4">{resetError}</div>}
              <form id="reset-pwd-form" onSubmit={handleResetPassword} className="space-y-4">
                <div>
                  <label className="kbb-label">Password Baru</label>
                  <input type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} placeholder="Min. 8 karakter" required className="kbb-input" />
                </div>
              </form>
            </div>
            <div className="kbb-modal-footer">
              <button type="button" onClick={() => setResetUser(null)} className="kbb-btn kbb-btn-ghost">Batal</button>
              <button type="submit" disabled={resetting || !newPassword} form="reset-pwd-form" className="kbb-btn kbb-btn-primary">{resetting ? 'Menyimpan...' : 'Simpan Password'}</button>
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog
        open={!!deleteUserId}
        title="Hapus User"
        message="Apakah Anda yakin ingin menghapus user ini? Semua data yang terkait dengan user ini juga akan dihapus."
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        onConfirm={handleDeleteUser}
        onCancel={() => setDeleteUserId(null)}
      />

      <ConfirmDialog
        open={!!deleteFormId}
        title="Hapus Form"
        message={`Apakah Anda yakin ingin menghapus form "${deleteFormTitle}" beserta seluruh data responsnya?`}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        onConfirm={handleDeleteForm}
        onCancel={() => setDeleteFormId(null)}
      />
    </div>
  );
}
