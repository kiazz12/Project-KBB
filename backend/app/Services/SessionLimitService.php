<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SessionLimitService
{
    public static function canLogin(User $user): bool
    {
        $count = DB::table('sessions')
            ->where('user_id', $user->id)
            ->count();

        if ($user->isSuperAdmin()) {
            return $count === 0;
        }

        return $count < 3;
    }

    public static function limit(User $user): int
    {
        return static::limitSessions($user);
    }

    public static function limitSessions(User $user): int
    {
        $currentSessionId = session()->getId();
        $deletedCount = 0;

        $userSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        if ($user->isSuperAdmin()) {
            $toDelete = $userSessions->where('id', '!=', $currentSessionId);
        } else {
            $maxSessions = 3;
            if ($userSessions->count() <= $maxSessions) {
                return 0;
            }
            $toDelete = $userSessions->slice($maxSessions);
        }

        if ($toDelete->isNotEmpty()) {
            $deletedCount = $toDelete->count();
            DB::table('sessions')
                ->whereIn('id', $toDelete->pluck('id'))
                ->delete();
        }

        return $deletedCount;
    }

    public static function limitTokens(User $user): int
    {
        $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();

        if ($user->isSuperAdmin()) {
            if ($tokens->count() <= 1) {
                return 0;
            }
            $toDelete = $tokens->slice(1);
        } else {
            $maxTokens = 3;
            if ($tokens->count() <= $maxTokens) {
                return 0;
            }
            $toDelete = $tokens->slice($maxTokens);
        }

        $count = $toDelete->count();
        $user->tokens()->whereIn('id', $toDelete->pluck('id'))->delete();

        return $count;
    }
}
