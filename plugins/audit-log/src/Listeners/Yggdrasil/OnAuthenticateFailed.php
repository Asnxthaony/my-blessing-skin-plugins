<?php

namespace AuditLog\Listeners\Yggdrasil;

use App\Models\User;

class OnAuthenticateFailed
{
    public function handle(User $user, $loginFails)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'yggdrasil-failed-authenticate',
            'details' => json_encode([
                'loginFails' => $loginFails,
            ]),
        ]);
    }
}
