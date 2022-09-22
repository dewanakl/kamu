<?php

namespace Core\Valid;

/**
 * Encrypt decrypt string
 * 
 * @class Hash
 * @package Core\Valid
 */
final class Hash
{
    /**
     * Algo Ciphering
     * 
     * @var string CIPHERING
     */
    public const CIPHERING = 'AES-128-CTR';

    /**
     * Encrypt dengan app key
     *
     * @param string $str
     * @return string
     */
    public static function encrypt(string $str): string
    {
        $app = explode(':', env('APP_KEY'));
        return openssl_encrypt($str, self::CIPHERING, $app[0], 0, $app[1]);
    }

    /**
     * Decrypt dengan app key
     *
     * @param string $str
     * @return string
     */
    public static function decrypt(string $str): string
    {
        $app = explode(':', env('APP_KEY'));
        return openssl_decrypt($str, self::CIPHERING, $app[0], 0, $app[1]);
    }

    /**
     * Make hash password
     *
     * @param string $value
     * @return string
     */
    public static function make(string $value): string
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    /**
     * Check hash password
     *
     * @param string $value
     * @param string $hashedValue
     * @return bool
     */
    public static function check(string $value, string $hashedValue): bool
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * Random string
     *
     * @param int $len
     * @return string
     */
    public static function rand(int $len): string
    {
        return bin2hex(random_bytes($len));
    }
}
