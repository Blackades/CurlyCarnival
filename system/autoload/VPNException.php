<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNException class
 * Custom exception for VPN-related errors
 */
class VPNException extends Exception
{
    // Validation errors (1000-1999)
    const ERR_INVALID_USERNAME = 1001;
    const ERR_USERNAME_EXISTS = 1002;
    const ERR_WEAK_PASSWORD = 1003;
    const ERR_INVALID_IP = 1004;
    const ERR_PASSWORD_MISMATCH = 1005;

    // Certificate errors (2000-2999)
    const ERR_CERT_GENERATION = 2001;
    const ERR_CERT_EXPIRED = 2002;
    const ERR_CA_MISSING = 2003;
    const ERR_CA_EXPIRING = 2004;
    const ERR_SERVER_CERT_MISSING = 2005;
    const ERR_SERVER_CERT_EXPIRING = 2006;
    const ERR_CERT_REVOCATION = 2007;

    // Script execution errors (3000-3999)
    const ERR_SCRIPT_EXECUTION = 3001;
    const ERR_SCRIPT_NOT_FOUND = 3002;
    const ERR_SCRIPT_PERMISSION = 3003;
    const ERR_SERVICE_RESTART = 3004;
    const ERR_SERVICE_STATUS = 3005;
    const ERR_AUTH_SCRIPT_BACKUP = 3006;
    const ERR_AUTH_SCRIPT_RESTORE = 3007;

    // Network errors (4000-4999)
    const ERR_CONNECTION_FAILED = 4001;
    const ERR_API_UNREACHABLE = 4002;
    const ERR_API_AUTH_FAILED = 4003;
    const ERR_VPN_UNREACHABLE = 4004;
    const ERR_PING_FAILED = 4005;

    // File system errors (5000-5999)
    const ERR_FILE_NOT_FOUND = 5001;
    const ERR_FILE_WRITE = 5002;
    const ERR_FILE_READ = 5003;
    const ERR_DIR_CREATE = 5004;
    const ERR_FILE_DELETE = 5005;

    // Database errors (6000-6999)
    const ERR_DB_INSERT = 6001;
    const ERR_DB_UPDATE = 6002;
    const ERR_DB_DELETE = 6003;
    const ERR_DB_QUERY = 6004;

    private $errorCode;
    private $context;

    /**
     * Constructor
     *
     * @param string $message
     * @param int $errorCode
     * @param array $context
     */
    public function __construct($message, $errorCode = 0, $context = [])
    {
        parent::__construct($message, $errorCode);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * Get error code
     *
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get context array
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
