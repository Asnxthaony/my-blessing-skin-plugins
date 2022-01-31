<?php

namespace AuditLog\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AgentExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('browser', [$this, 'mGetBrowser']),
            new TwigFilter('location', [$this, 'mGetLocation']),
        ];
    }

    public function mGetBrowser($userAgent)
    {
        return getBrowser($userAgent);
    }

    public function mGetLocation($ip)
    {
        return getLocation($ip);
    }
}
