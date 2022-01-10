<?php

namespace Asnxthaony\MiniLogin\Models;

use Illuminate\Database\Eloquent\Model;

class WechatUser extends Model
{
    protected $table = 'wechat_users';

    protected $fillable = [
        'user_id',
        'nickname',
        'open_id',
        // 'union_id',
    ];
}
