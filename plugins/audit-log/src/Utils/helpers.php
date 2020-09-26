<?php

use AuditLog\Models\AuditLog;
use Carbon\Carbon;
use Vectorface\Whip\Whip;
use Illuminate\Support\Facades\Log;

if (!function_exists('audit_log')) {
    function audit_log($params)
    {
        $data = array_merge([
            'user_id' => 0,
            'action' => 'undefined',
            'details' => '[]',
            'ip' => (new Whip())->getValidIpAddress(),
            'user_agent' => request()->userAgent(),
            'time' => Carbon::now(),
        ], $params);

        AuditLog::create($data);
    }
}
