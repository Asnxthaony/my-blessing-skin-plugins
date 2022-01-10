<?php

namespace AuditLog\Listeners\Yggdrasil;

use App\Models\User;

class OnAuthenticateSucceeded
{
    public function handle(User $user, $authType)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'yggdrasil-authenticate',
            'details' => json_encode([
                'authType' => $authType,
            ]),
        ]);
    }
}
