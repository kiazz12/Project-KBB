import { useState, useEffect, useCallback } from 'react';
import { UsersIcon, PencilSquareIcon, TrashIcon, PlusIcon } from '@heroicons/react/24/outline';
import { formService } from '../services';
import LoadingSpinner from '../components/LoadingSpinner';
import EmptyState from '../components/EmptyState';
import ConfirmDialog from '../components/ConfirmDialog';

const roleColors: Record<string, string> = {
  super_admin: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
  admin: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
  operator: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
  viewer: 'bg-white/5 text-white/50 border-white/10',
};

export default function UsersIndex() {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editUser, setEditUser] = useState<any>(null);
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [form, setForm] = useState({ name: '', email: '', password: '', role: 'admin', nip: '', opd: '' });
  const [saving, setSaving] = useState(false);

  const fetchUsers = useCallback(async () => {
    setLoading(true);
    try {
      const res = await formService.getUsers();
      setUsers(res);
    } catch { /* ignore */ } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchUsers(); }, [fetchUsers]);

  const openCreate = () => {
    setEditUser(null);
    setForm({ name: '', email: '', password: '', role: 'admin', nip: '', opd: '' });
    setModalOpen(true);
  };

  const openEdit = (user: any) => {
    setEditUser(user);
    setForm({ name: user.name, email: user.email, password: '', role: user.role, nip: user.nip ?? '', opd: user.opd ?? '' });
    setModalOpen(true);
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      if (editUser) {
        const payload: Record<string, any> = { name: form.name, email: form.email, role: form.role, nip: form.nip, opd: form.opd };
        if (form.password) payload.password = form.password;
        await formService.updateUser(editUser.id, payload);
      } else {
        await formService.createUser({ ...form, password: form.password });
      }
      setModalOpen(false);
      fetchUsers();
    } catch { /* ignore */ } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      await formService.deleteUser(deleteId);
      setDeleteId(null);
      fetchUsers();
    } catch { /* ignore */ }
  };

  return (
    <div className="space-y-6 animate-fade-in-up">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gold-400/10 border border-gold-400/20 flex items-center justify-center flex-shrink-0">
            <UsersIcon className="h-6 w-6 text-gold-400" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-white">Users</h1>
            <p className="text-white/40 text-sm mt-0.5">Kelola pengguna sistem</p>
          </div>
        </div>
        <button onClick={openCreate} className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-gold-400 to-gold-500 text-white font-semibold text-sm hover:shadow-lg hover:shadow-gold-400/25 transition flex items-center gap-2">
          <PlusIcon className="h-4 w-4" /> Tambah Pengguna
        </button>
      </div>

      {loading ? (
        <LoadingSpinner />
      ) : users.length === 0 ? (
        <EmptyState icon={<UsersIcon className="h-12 w-12" />} title="Tidak ada pengguna" description="Tambahkan pengguna baru untuk memulai"
          action={<button onClick={openCreate} className="px-5 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition">Tambah Pengguna</button>} />
      ) : (
        <div className="overflow-x-auto rounded-2xl border border-white/10 bg-white/5">
          <table className="w-full">
            <thead>
              <tr className="bg-white/5">
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">Nama</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">Email</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">Role</th>
                <th className="px-4 py-3 text-left text-xs text-white/40 uppercase tracking-wider font-semibold">OPD</th>
                <th className="px-4 py-3 text-right text-xs text-white/40 uppercase tracking-wider font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-white/5">
              {users.map((user) => (
                <tr key={user.id} className="hover:bg-white/5 transition">
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-gradient-to-br from-gold-400 to-gold-500 flex items-center justify-center text-white font-semibold text-xs shadow-sm">
                        {user.name?.charAt(0)?.toUpperCase()}
                      </div>
                      <span className="text-sm font-medium text-white">{user.name}</span>
                    </div>
                  </td>
                  <td className="px-4 py-3 text-sm text-white/40">{user.email}</td>
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border ${roleColors[user.role] || 'bg-white/5 text-white/50 border-white/10'}`}>
                      {user.role?.replace('_', ' ')}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-sm text-white/40">{user.opd || '-'}</td>
                  <td className="px-4 py-3 text-right">
                    <button onClick={() => openEdit(user)} className="p-1.5 rounded-lg text-white/30 hover:text-gold-400 hover:bg-white/5 transition">
                      <PencilSquareIcon className="h-3.5 w-3.5" />
                    </button>
                    <button onClick={() => setDeleteId(user.id)} className="p-1.5 rounded-lg text-white/30 hover:text-red-400 hover:bg-white/5 transition ml-1">
                      <TrashIcon className="h-3.5 w-3.5" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {modalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in" onClick={() => setModalOpen(false)}>
          <div className="bg-[#0e0e24] border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-2xl shadow-black/40 animate-fade-in-up" onClick={(e) => e.stopPropagation()}>
            <h2 className="text-lg font-semibold text-white mb-5">{editUser ? 'Edit Pengguna' : 'Tambah Pengguna'}</h2>
            <div className="space-y-4">
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Nama</label>
                <input type="text" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              </div>
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Email</label>
                <input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              </div>
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Password {editUser && '(kosongkan jika tidak diganti)'}</label>
                <input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required={!editUser}
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              </div>
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">Role</label>
                <select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition">
                  <option value="super_admin">Super Admin</option>
                  <option value="admin">Admin</option>
                  <option value="operator">Operator</option>
                  <option value="viewer">Viewer</option>
                </select>
              </div>
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">NIP</label>
                <input type="text" value={form.nip} onChange={(e) => setForm({ ...form, nip: e.target.value })}
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              </div>
              <div>
                <label className="block text-xs font-medium text-white/50 uppercase tracking-wider mb-1.5">OPD</label>
                <input type="text" value={form.opd} onChange={(e) => setForm({ ...form, opd: e.target.value })}
                  className="w-full px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/10 transition" />
              </div>
              <div className="flex justify-end gap-3 pt-3 border-t border-white/10">
                <button onClick={() => setModalOpen(false)} className="px-4 py-2 rounded-xl text-sm text-white/50 hover:text-white/70 hover:bg-white/5 transition">Batal</button>
                <button onClick={handleSave} disabled={saving} className="px-4 py-2 rounded-xl bg-kbb-700 hover:bg-kbb-600 text-white text-sm font-semibold transition">
                  {saving ? 'Menyimpan...' : editUser ? 'Perbarui' : 'Simpan'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog open={!!deleteId} title="Hapus Pengguna" message="Apakah Anda yakin ingin menghapus pengguna ini?" confirmLabel="Hapus" cancelLabel="Batal" variant="danger" onConfirm={handleDelete} onCancel={() => setDeleteId(null)} />
    </div>
  );
}
