<?php

namespace AuditLog\Listeners;

use App\Models\User;

class OnProfileUpdated
{
    public function handle(User $user, $action, $addition)
    {
        switch ($action) {
            case 'password':
                return audit_log([
                    'user_id' => $user->uid,
                    'action' => 'password-updated',
                ]);
            case 'email':
                return audit_log([
                    'user_id' => $user->uid,
                    'action' => 'email-updated',
                    'details' => json_encode([
                        'newEmail' => $user->email,
                    ]),
                ]);
        }
    }
}
