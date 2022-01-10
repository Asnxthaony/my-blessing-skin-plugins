<?php

namespace Asnxthaony\Smrz;

use DateTime;

class IdValidator
{
    private $_areaCodeList = [];

    public function __construct()
    {
        $this->_areaCodeList = require __DIR__.'/../data/areaCode.php';
    }

    public function isValid($id)
    {
        $code = $this->_checkIdArgument($id);

        if (empty($code)) {
            return false;
        }

        if (!$this->_checkAreaCode($code['areaCode'])) {
            return false;
        }

        if (!$this->_checkBirthdayCode($code['birthdayCode'])) {
            return false;
        }

        if (!$this->_checkOrderCode($code['orderCode'])) {
            return false;
        }

        $checksumDigit = $this->_generatorChecksumDigit($code['body']);

        return $checksumDigit == $code['checksumDigit'];
    }

    private function _checkIdArgument($id)
    {
        $id = strtoupper($id);
        $length = strlen($id);

        if ($length === 15) {
            return false;
        }

        if ($length === 18) {
            return $this->_generateLongType($id);
        }

        return false;
    }

    private function _generateLongType($id)
    {
        preg_match('/((.{6})(.{8})(.{3}))(.)/', $id, $matches);

        return [
            'body' => $matches[1],
            'areaCode' => $matches[2],
            'birthdayCode' => $matches[3],
            'orderCode' => $matches[4],
            'checksumDigit' => $matches[5],
        ];
    }

    private function _checkAreaCode($areaCode)
    {
        return isset($this->_areaCodeList[$areaCode]);
    }

    private function _checkOrderCode($orderCode)
    {
        return strlen($orderCode) === 3;
    }

    private function _checkBirthdayCode($birthdayCode)
    {
        $date = DateTime::createFromFormat($format = 'Ymd', $birthdayCode);

        return $date->format($format) === $birthdayCode && (int) $date->format('Y') >= 1900;
    }

    private function _generatorChecksumDigit($body)
    {
        $weight = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1];

        $sum = 0;
        $array = str_split($body);
        $count = count($array);

        for ($j = 0; $j < $count; $j++) {
            $sum += ((int) $array[$j] * $weight[$j]);
        }

        $checksumDigit = (12 - ($sum % 11)) % 11;

        return $checksumDigit == 10 ? 'X' : (string) $checksumDigit;
    }
}
