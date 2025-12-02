<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNInputValidator class
 * Validates VPN-related user inputs
 */
class VPNInputValidator
{
    /**
     * Validate VPN username
     * Must be 3-32 alphanumeric characters, underscore, or hyphen
     *
     * @param string $username
     * @return bool
     * @throws VPNException
     */
    public static function validateUsername($username)
    {
        if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username)) {
            throw new VPNException(
                'Invalid username format. Must be 3-32 alphanumeric characters, underscore, or hyphen.',
                VPNException::ERR_INVALID_USERNAME
            );
        }
        return true;
    }

    /**
     * Validate VPN password
     * Minimum 8 characters with complexity requirements
     *
     * @param string $password
     * @return bool
     * @throws VPNException
     */
    public static function validatePassword($password)
    {
        if (strlen($password) < 8) {
            throw new VPNException(
                'Password must be at least 8 characters long.',
                VPNException::ERR_WEAK_PASSWORD
            );
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new VPNException(
                'Password must contain at least one uppercase letter.',
                VPNException::ERR_WEAK_PASSWORD
            );
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new VPNException(
                'Password must contain at least one lowercase letter.',
                VPNException::ERR_WEAK_PASSWORD
            );
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new VPNException(
                'Password must contain at least one number.',
                VPNException::ERR_WEAK_PASSWORD
            );
        }

        return true;
    }

    /**
     * Validate IP address
     *
     * @param string $ip
     * @return bool
     * @throws VPNException
     */
    public static function validateIPAddress($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new VPNException(
                'Invalid IP address format.',
                VPNException::ERR_INVALID_IP
            );
        }
        return true;
    }

    /**
     * Sanitize shell argument to prevent command injection
     *
     * @param string $arg
     * @return string
     */
    public static function sanitizeShellArg($arg)
    {
        return escapeshellarg($arg);
    }
}
