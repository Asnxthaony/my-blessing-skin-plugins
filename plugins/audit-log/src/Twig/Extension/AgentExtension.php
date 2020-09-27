<?php

namespace AuditLog\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AgentExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('browser', [$this, 'getBrowser'])
        ];
    }

    public function getBrowser($userAgent)
    {
        $result = new \WhichBrowser\Parser($userAgent);

        return $result->toString();
    }
}
