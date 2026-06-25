import { useState, useEffect, useMemo } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import { userService } from '../services';
import LoadingSpinner from '../components/LoadingSpinner';
import ConfirmDialog from '../components/ConfirmDialog';
import type { User } from '../types';

const PER_PAGE = 10;
const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

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

export default function UsersIndex() {
  const { users: initialUsers } = usePage().props as unknown as { users: User[] };
  const [users, setUsers] = useState<User[]>(() => {
    if (Array.isArray(initialUsers) && initialUsers.length > 0) return initialUsers;
    return [];
  });
  const [loading, setLoading] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [error, setError] = useState('');

  const [showForm, setShowForm] = useState(false);
  const [editUser, setEditUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<FormData>(initialFormData);
  const [saving, setSaving] = useState(false);
  const [formError, setFormError] = useState('');

  const [deleteUserId, setDeleteUserId] = useState<number | null>(null);

  const [letterFilter, setLetterFilter] = useState('');
  const [opdFilter, setOpdFilter] = useState('');
  const [currentPage, setCurrentPage] = useState(1);

  useEffect(() => { fetchUsers(); }, []);

  const opdList = useMemo(() => {
    const ops = new Set<string>();
    users.forEach((u) => { if (u.opd) ops.add(u.opd); });
    return Array.from(ops).sort();
  }, [users]);

  const fetchUsers = async () => {
    setLoading(true);
    setError('');
    try {
      const res = await userService.getUsers({ all: 1 });
      if (Array.isArray(res)) {
        setUsers(res);
      } else {
        console.warn('Unexpected users response format:', res);
        setError('Format data tidak sesuai. Silakan coba lagi.');
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || 'Gagal memuat data pengguna.';
      const status = err.response?.status;
      setError(status ? `Error ${status}: ${msg}` : msg);
    } finally {
      setLoading(false);
    }
  };

  const filteredUsers = useMemo(() => {
    let result = users;
    if (searchQuery) {
      const q = searchQuery.toLowerCase();
      result = result.filter((u) =>
        u.name.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q) ||
        (u.nip && u.nip.toLowerCase().includes(q)) ||
        (u.opd && u.opd.toLowerCase().includes(q)) ||
        u.role.toLowerCase().includes(q)
      );
    }
    if (letterFilter) {
      result = result.filter((u) => u.name.charAt(0).toUpperCase() === letterFilter);
    }
    if (opdFilter) {
      result = result.filter((u) => u.opd === opdFilter);
    }
    return result;
  }, [users, searchQuery, letterFilter, opdFilter]);

  const totalPages = Math.max(1, Math.ceil(filteredUsers.length / PER_PAGE));
  const safePage = Math.min(currentPage, totalPages);
  const pagedUsers = filteredUsers.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const handleFilterChange = (type: string, value: string) => {
    if (type === 'letter') {
      setLetterFilter(value === letterFilter ? '' : value);
    } else if (type === 'opd') {
      setOpdFilter(value === opdFilter ? '' : value);
    }
    setCurrentPage(1);
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
      await fetchUsers();
    } catch (err: any) {
      setFormError(err.response?.data?.message || 'Gagal menyimpan user.');
    } finally {
      setSaving(false);
    }
  };

  const openCreate = () => {
    setEditUser(null);
    setFormData(initialFormData);
    setShowForm(true);
    setFormError('');
  };

  const handleDeleteUser = async () => {
    if (!deleteUserId) return;
    try {
      await userService.deleteUser(deleteUserId);
      setUsers((prev) => prev.filter((u) => u.id !== deleteUserId));
      setDeleteUserId(null);
    } catch { /* ignore */ }
  };

  if (loading) return <LoadingSpinner />;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Pengguna</h1>
          <p className="text-sm text-gray-500 mt-1">{users.length} akun terdaftar</p>
        </div>
        <button onClick={openCreate} className="kbb-btn kbb-btn-primary">
          <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" /></svg>
          User Baru
        </button>
      </div>

      <div className="relative max-w-md">
        <svg className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
        <input type="text" value={searchQuery} onChange={(e) => { setSearchQuery(e.target.value); setCurrentPage(1); }} placeholder="Cari pengguna..." className="kbb-input pl-10" />
      </div>

      <div className="flex flex-wrap gap-3">
        <div className="flex items-center gap-1 flex-wrap">
          <button onClick={() => handleFilterChange('letter', '')}
            className={`px-2.5 py-1 rounded-lg text-xs font-medium transition ${
              !letterFilter ? 'bg-[#003778] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}>Semua</button>
          {ALPHABET.map((l) => (
            <button key={l} onClick={() => handleFilterChange('letter', l)}
              className={`w-7 h-7 rounded-lg text-xs font-medium transition ${
                letterFilter === l ? 'bg-[#003778] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              }`}>{l}</button>
          ))}
        </div>
        <select value={opdFilter} onChange={(e) => handleFilterChange('opd', e.target.value)}
          className="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 focus:outline-none focus:border-[#003778]">
          <option value="">Semua OPD</option>
          {opdList.map((o) => <option key={o} value={o}>{o}</option>)}
        </select>
      </div>

      {error && (
        <div role="alert" className="flex items-center gap-2.5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
          <svg className="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span className="flex-1">{error}</span>
          <button onClick={fetchUsers} className="ml-auto text-xs px-3 py-1.5 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 font-medium transition">Ulangi</button>
        </div>
      )}

      <div className="kbb-table-wrapper">
        <table className="kbb-table">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th className="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {pagedUsers.map((user) => (
              <tr key={user.id}
                onClick={() => router.visit(`/users/${user.id}`)}
                className="cursor-pointer hover:bg-gray-50 transition-colors"
              >
                  <td className="px-4 py-3.5">
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-full bg-[#C8A45C]/20 border border-[#C8A45C]/30 flex items-center justify-center text-sm font-semibold text-[#8B6F3A] flex-shrink-0">
                        {user.name.charAt(0).toUpperCase()}
                      </div>
                      <div className="min-w-0">
                        <p className="text-sm font-medium text-gray-900 truncate">{user.name}</p>
                        {user.nip && <p className="text-xs text-gray-400 truncate">NIP. {user.nip}</p>}
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-3.5 text-sm text-gray-600">{user.email}</td>
                  <td className="px-4 py-3.5"><RoleBadge role={user.role} /></td>
                  <td className="px-4 py-3.5 text-right" onClick={(e) => e.stopPropagation()}>
                    <Link href={`/users/${user.id}`}
                      className="px-2.5 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                      <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                      Detail
                    </Link>
                  </td>
                </tr>
              ))}
              {pagedUsers.length === 0 && (
                <tr><td colSpan={4} className="px-4 py-12 text-center text-sm text-gray-400">{searchQuery || letterFilter || opdFilter ? 'Tidak ada pengguna yang cocok.' : 'Belum ada pengguna.'}</td></tr>
              )}
            </tbody>
          </table>

        {totalPages > 1 && (
          <div className="flex items-center justify-between px-4 py-3 border-t border-gray-100">
            <span className="text-xs text-gray-500">
              {filteredUsers.length} pengguna · Halaman {safePage} dari {totalPages}
            </span>
            <div className="flex gap-1">
              <button onClick={() => setCurrentPage(safePage - 1)} disabled={safePage <= 1}
                className="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-200 transition disabled:opacity-30 disabled:cursor-not-allowed">
                Prev
              </button>
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
                <button key={p} onClick={() => setCurrentPage(p)}
                  className={`w-7 h-7 rounded-lg text-xs font-medium transition ${
                    p === safePage ? 'bg-[#003778] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                  }`}>{p}</button>
              ))}
              <button onClick={() => setCurrentPage(safePage + 1)} disabled={safePage >= totalPages}
                className="px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-200 transition disabled:opacity-30 disabled:cursor-not-allowed">
                Next
              </button>
            </div>
          </div>
        )}
      </div>

      {showForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" onClick={() => setShowForm(false)}>
          <div className="kbb-modal" onClick={(e) => e.stopPropagation()}>
            <div className="kbb-modal-header">
              <span>{editUser ? 'Edit User' : 'Buat User Baru'}</span>
              <button onClick={() => setShowForm(false)} className="p-1 rounded-lg hover:bg-gray-100 transition" aria-label="Tutup">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
            <div className="kbb-modal-body">
              {formError && <div role="alert" className="kbb-alert kbb-alert-danger mb-4">{formError}</div>}
              <form id="user-form" onSubmit={handleSave} className="space-y-4">
                <div>
                  <label className="kbb-label">Nama</label>
                  <input type="text" value={formData.name} onChange={(e) => setFormData({ ...formData, name: e.target.value })} placeholder="Nama lengkap" required className="kbb-input" />
                </div>
                <div>
                  <label className="kbb-label">Email</label>
                  <input type="email" value={formData.email} onChange={(e) => setFormData({ ...formData, email: e.target.value })} placeholder="email@contoh.com" required={!editUser} disabled={!!editUser} className="kbb-input" />
                </div>
                {!editUser ? (
                  <div>
                    <label className="kbb-label">Password</label>
                    <input type="password" value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })} placeholder="Min. 8 karakter" required className="kbb-input" />
                  </div>
                ) : (
                  <div>
                    <label className="kbb-label">Password Baru <span className="text-gray-400 font-normal">(kosongkan jika tidak diganti)</span></label>
                    <input type="password" value={formData.password} onChange={(e) => setFormData({ ...formData, password: e.target.value })} placeholder="Min. 8 karakter" className="kbb-input" />
                  </div>
                )}
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
              <button type="submit" disabled={saving} form="user-form" className="kbb-btn kbb-btn-primary">{saving ? 'Menyimpan...' : editUser ? 'Simpan Perubahan' : 'Buat User'}</button>
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
    </div>
  );
}
