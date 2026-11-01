<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        $users->through(fn (User $user): array => $this->mapUser($user));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['super_admin', 'admin'])],
            'nip' => 'nullable|string|max:255',
            'opd' => 'nullable|string|max:255',
            'opd_id' => 'nullable|integer|exists:opds,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nip' => $request->nip,
            'opd' => $request->opd,
            'opd_id' => $request->opd_id,
        ]);

        AuditService::log('user.created', $user, "Admin panel: User '{$user->name}' created");

        return redirect(route('admin.users.index'))->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Form', [
            'user' => $this->mapUser($user),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['sometimes', Rule::in(['super_admin', 'admin'])],
            'nip' => 'nullable|string|max:255',
            'opd' => 'nullable|string|max:255',
            'opd_id' => 'nullable|integer|exists:opds,id',
        ]);

        $data = $request->only(['name', 'email', 'role', 'nip', 'opd', 'opd_id']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        AuditService::log('user.updated', $user, "Admin panel: User '{$user->name}' updated");

        return redirect(route('admin.users.index'))->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        AuditService::log('user.deleted', $user, "Admin panel: User '{$user->name}' deleted");

        return redirect(route('admin.users.index'))->with('success', 'User berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_super_admin' => $user->isSuperAdmin(),
            'nip' => $user->nip,
            'opd' => $user->opd,
            'created_at' => $user->created_at?->format('d/m/Y'),
        ];
    }
}
