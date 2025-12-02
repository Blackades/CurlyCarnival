<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNPasswordManager class
 * Handles password hashing and encryption for VPN credentials
 */
class VPNPasswordManager
{
    /**
     * Hash password using Argon2ID
     * Used for storing VPN password hash in database for audit/recovery
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password against hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Encrypt password for storage using AES-256-CBC
     * Used for symmetric encryption of plain password in OpenVPN auth script
     *
     * @param string $password
     * @param string $key
     * @return string
     */
    public static function encryptForStorage($password, $key)
    {
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt password from storage
     *
     * @param string $encrypted
     * @param string $key
     * @return string
     */
    public static function decryptFromStorage($encrypted, $key)
    {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
