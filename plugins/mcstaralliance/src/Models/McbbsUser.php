<?php

namespace mcstaralliance\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $forum_uid
 * @property string $forum_username
 * @property int    $forum_groupid
 * @property string $created_at
 * @property string $updated_at
 */
class McbbsUser extends Model
{
    protected $table = 'connect_mcbbs';

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'forum_uid' => 'integer',
        'forum_groupid' => 'integer',
    ];
}
