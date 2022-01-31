<?php

namespace AuditLog\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\LaravelSearchString\Concerns\SearchString;

class AuditLog extends Model
{
    use SearchString;

    protected $table = 'audit_logs';

    protected $appends = [
        'region',
        'formatted_user_agent',
    ];

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $searchStringColumns = [
        'user_id',
        'action' => ['searchable' => true],
        'ip' => ['searchable' => true],
        'user_agent' => ['searchable' => true],
        'created_at' => ['date' => true],
    ];

    protected function getRegionAttribute()
    {
        return getLocation($this->ip);
    }

    protected function getFormattedUserAgentAttribute()
    {
        return getBrowser($this->user_agent);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
