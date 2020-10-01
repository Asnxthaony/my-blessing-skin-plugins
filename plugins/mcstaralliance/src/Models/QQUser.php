<?php

namespace mcstaralliance\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $qq_id
 * @property string $created_at
 */
class QQUser extends Model
{
    protected $table = 'connect_qq';

    public const UPDATED_AT = null;

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'qq_id' => 'integer',
    ];
}
