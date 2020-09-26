<?php

namespace AuditLog\Listeners;

use App\Models\User;

class OnUserDeleted
{
    public function handle(User $user)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'user-deleted',
        ]);
    }
}
