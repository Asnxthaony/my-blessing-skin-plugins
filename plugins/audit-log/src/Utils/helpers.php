<?php

use AuditLog\Models\AuditLog;
use Carbon\Carbon;
use Vectorface\Whip\Whip;

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

if (!function_exists('getBrowser')) {
    function getBrowser($userAgent)
    {
        $result = new \WhichBrowser\Parser($userAgent);

        return $result->toString();
    }
}

if (!function_exists('getLocation')) {
    function getLocation($ip)
    {
        $location = '未知';

        if ($ip === '172.22.22.1') {
            return 'Game Server #01';
        } elseif ($ip === '255.255.255.255') {
            return '运营中心';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $city = new \ipip\db\City('/usr/local/share/GeoIP/v4.ipdb');
        } else {
            $city = new \ipip\db\City('/usr/local/share/GeoIP/v6.ipdb');
        }

        try {
            $location = $city->findMap($ip, 'CN');
            return $location['country_name'] . $location['region_name'] . $location['city_name'];
        } catch (\InvalidArgumentException $ex) {
            // return $ex->getMessage();
        }

        return $location;
    }
}
