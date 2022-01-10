<?php

namespace AuditLog\Listeners\Yggdrasil;

use App\Models\Player;
use App\Models\User;

class OnJoinServer
{
    public function handle(User $user, Player $player, $serverId)
    {
        return audit_log([
            'user_id' => $user->uid,
            'action' => 'yggdrasil-join-server',
            'details' => json_encode([
                'playerName' => $player->name,
                'serverId' => $serverId,
            ]),
        ]);
    }
}
