<?php
declare(strict_types=1);

namespace Mizam\Service;

use Exception;

class Random
{
    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    static public function generateToken(int $length): string
    {
        return static::base64url_encode(random_bytes($length));

    }

    /**
     * @return string
     * @throws Exception
     */
    static public function generateShortToken(): string
    {
        return static::generateToken(9);
    }

    /**
     * @return string
     * @throws Exception
     */
    static public function generateLongToken(): string
    {
        return static::generateToken(33);
    }


    static public function base64url_encode(string $str): string
    {
        return trim(strtr(base64_encode($str), "+/", "-_"), "=");
    }

    static public function base64url_decode(string $str): string
    {
        return base64_decode(strtr(str_repeat("=", (strlen($str) % 4)), "-_", "+/"));
    }
}
