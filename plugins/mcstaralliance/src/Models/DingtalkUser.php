<?php

namespace mcstaralliance\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $user_id
 * @property string $nickname
 * @property string $open_id
 * @property string $union_id
 * @property string $created_at
 * @property string $updated_at
 */
class DingtalkUser extends Model
{
    protected $table = 'connect_dingtalk';

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];
}
