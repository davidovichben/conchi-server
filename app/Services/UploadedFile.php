<?php

namespace App\Services;
class Helpers
{
    public static function decodeBase64Binary($string)
    {
        if (!$string) {
            return null;
        }

        $extStart = strpos($string, '/');
        $base64Start = strpos($string, 'base64,');

        return collect([
            'bin' 	=> base64_decode(substr($string, $base64Start + 7)),
            'ext'	=> substr($string, $extStart + 1, $base64Start - $extStart - 2)
        ]);
    }
}
