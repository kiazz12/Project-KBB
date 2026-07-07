<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;
use App\Services\SessionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;
        SessionLimitService::limitTokens($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Login berhasil.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'Data user berhasil diambil.',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Password saat ini tidak sesuai.',
            ], 403);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        AuditService::log('password.changed', $user, "User '{$user->name}' changed password via API");

        User::where('role', 'super_admin')->get()->each(fn($sa) =>
            Notification::create([
                'user_id' => $sa->id,
                'type' => 'password_changed',
                'message' => "Admin {$user->name} ({$user->email}) mengubah password akun mereka.",
                'data' => ['admin_id' => $user->id, 'admin_name' => $user->name, 'admin_email' => $user->email],
            ])
        );

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Password berhasil diubah.',
        ]);
    }
}
