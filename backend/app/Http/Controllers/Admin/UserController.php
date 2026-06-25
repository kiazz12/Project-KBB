<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form');
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'nip' => $request->nip,
            'opd' => $request->opd,
        ]);

        AuditService::log('user.created', $user, "Admin panel: User '{$user->name}' created");

        return redirect(route('admin.users.index'))->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
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
        ]);

        $data = $request->only(['name', 'email', 'role', 'nip', 'opd']);
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
}
