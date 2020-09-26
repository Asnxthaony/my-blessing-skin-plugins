<?php

namespace AuditLog\Listeners;

use App\Models\User;

class OnUserLoginFailed
{
    public function handle(User $user, $loginFails)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'failed-login',
            'details' => json_encode([
                'loginFails' => $loginFails,
            ]),
        ]);
    }
}
