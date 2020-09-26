<?php

namespace AuditLog\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'details', 'ip', 'user_agent'];
}
