<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSessionService
{
    public function revoke(User $user): void
    {
        if (config('session.driver') === 'database') {
            $connection = config('session.connection');
            $table = (string) config('session.table', 'sessions');

            $query = $connection
                ? DB::connection($connection)->table($table)
                : DB::table($table);

            $query
                ->where('user_id', $user->getKey())
                ->delete();
        }

        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->saveQuietly();
    }
}
