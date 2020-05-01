<?php

namespace mcstaralliance;

use Illuminate\Database\Eloquent\Model;

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
