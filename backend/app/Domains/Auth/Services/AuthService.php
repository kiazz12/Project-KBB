<?php

namespace App\Domains\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthService
{
    /**
     * Authenticate user and return token
     */
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user by revoking token
     */
    public function logout(User $user): bool
    {
        $user->currentAccessToken()?->delete();
        return true;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);
        
        // Revoke all existing tokens
        $user->tokens()->delete();
        
        return true;
    }

    /**
     * Get current user
     */
    public function getCurrentUser(User $user): User
    {
        return $user;
    }
}
