<?php

namespace AuditLog\Listeners;

use App\Models\User;

class OnLoginSucceeded
{
    public function handle(User $user)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'login',
        ]);
    }
}
