<?php

namespace AuditLog\Listeners\Yggdrasil;

use App\Models\Player;
use App\Models\User;

class OnHasJoinedServer
{
    public function handle(User $user, Player $player, $serverId, $ip)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'yggdrasil-has-joined-server',
            'details' => json_encode([
                'playerName' => $player->name,
                'serverId' => $serverId,
            ]),
            'ip' => $ip,
        ]);
    }
}
