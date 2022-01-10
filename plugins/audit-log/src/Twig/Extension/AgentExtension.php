<?php

namespace AuditLog\Twig\Extension;

use GeoIp2\Database\Reader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AgentExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('browser', [$this, 'getBrowser']),
            new TwigFilter('location', [$this, 'getLocation']),
        ];
    }

    public function getBrowser($userAgent)
    {
        $result = new \WhichBrowser\Parser($userAgent);

        return $result->toString();
    }

    public function getLocation($ip)
    {
        $location = 'Unknown';

        $reader = new Reader('/usr/local/share/GeoIP/GeoLite2-City.mmdb');

        try {
            $record = $reader->city($ip);

            $country = $record->country;
            $city = $record->city;

            if ($city && $city->names) {
                $location = $country->names['zh-CN'].$city->names['zh-CN'];
            } else {
                $location = $country->names['zh-CN'];
            }
        } catch (\GeoIp2\Exception\AddressNotFoundException $ex) {
        }

        return $location;
    }
}
