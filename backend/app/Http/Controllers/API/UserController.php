<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            $users = User::latest()->get();
        } else {
            $users = User::latest()->paginate($request->per_page ?? 15);
        }

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Daftar user berhasil diambil.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in([UserRole::SuperAdmin->value, UserRole::Admin->value])],
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

        AuditService::log('user.created', $user, "User '{$user->name}' created");

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User berhasil dibuat.',
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'role' => ['sometimes', Rule::in([UserRole::SuperAdmin->value, UserRole::Admin->value])],
            'nip' => 'nullable|string|max:255',
            'opd' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['name', 'email', 'role', 'nip', 'opd', 'opd_id']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $old = $user->toArray();
        $user->update($data);

        AuditService::log('user.updated', $user, "User '{$user->name}' updated", $old, $user->toArray());

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Tidak dapat menghapus akun sendiri.',
            ], 403);
        }

        $user->delete();

        AuditService::log('user.deleted', $user, "User '{$user->name}' deleted");

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'User berhasil dihapus.',
        ]);
    }

    public function forms(Request $request, User $user): JsonResponse
    {
        $forms = Form::where('user_id', $user->id)
            ->withCount(['fields', 'submissions'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $forms,
            'message' => 'Daftar form user berhasil diambil.',
        ]);
    }
}
