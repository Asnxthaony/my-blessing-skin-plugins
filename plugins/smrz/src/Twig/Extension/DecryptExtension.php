<?php

namespace Asnxthaony\Smrz\Twig\Extension;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DecryptExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('decrypt', [$this, 'decryptString']),
        ];
    }

    public function decryptString($data)
    {
        try {
            $result = Crypt::decryptString($data);
        } catch (DecryptException $e) {
            $result = '解密敏感信息失败';
        }

        return $result;
    }
}
