<?php

namespace Core\Valid;

/**
 * Encrypt decrypt string
 * 
 * @class Hash
 * @package \Core\Valid
 */
final class Hash
{
    /**
     * Algo Ciphering
     * 
     * @var string CIPHERING
     */
    public const CIPHERING = 'aes-256-cbc';

    /**
     * Algo Hash
     * 
     * @var string HASH
     */
    public const HASH = 'sha3-512';

    /**
     * Encrypt dengan app key
     *
     * @param string $str
     * @return string
     */
    public static function encrypt(string $str): string
    {
        $app = explode(':', env('APP_KEY'));
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHERING));
        $encrypted = openssl_encrypt($str, self::CIPHERING, $app[1] ?? static::rand(5), OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . hash_hmac(self::HASH, $encrypted, $app[0], true) . $encrypted);
    }

    /**
     * Decrypt dengan app key
     *
     * @param string $str
     * @return string|null
     */
    public static function decrypt(string $str): string|null
    {
        $app = explode(':', env('APP_KEY'));
        $mix = base64_decode($str);

        $iv = openssl_cipher_iv_length(self::CIPHERING);
        $encrypted = substr($mix, $iv + 64);

        if (!hash_equals(substr($mix, $iv, 64), hash_hmac(self::HASH, $encrypted, $app[0], true))) {
            return null;
        }

        return openssl_decrypt($encrypted, self::CIPHERING, $app[1] ?? static::rand(5), OPENSSL_RAW_DATA, substr($mix, 0, $iv));
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
