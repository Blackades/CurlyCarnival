<?php

/**
 * Router Model - ORM extensions for tbl_routers
 * Provides VPN-related helper methods for remote router management
 */

class Router
{
    /**
     * Check if router is a remote type (requires VPN connection)
     * 
     * @param object $router ORM instance of router
     * @return bool True if connection_type is 'remote'
     */
    public static function isRemote($router)
    {
        return isset($router->connection_type) && $router->connection_type === 'remote';
    }

    /**
     * Get current VPN connection status
     * 
     * @param object $router ORM instance of router
     * @return string Current ovpn_status value ('connected', 'disconnected', 'error', 'pending')
     */
    public static function getVPNStatus($router)
    {
        return isset($router->ovpn_status) ? $router->ovpn_status : 'pending';
    }

    /**
     * Retrieve certificate details from tbl_vpn_certificates
     * 
     * @param object $router ORM instance of router
     * @return object|false Certificate record or false if not found
     */
    public static function getCertificateInfo($router)
    {
        if (!isset($router->id)) {
            return false;
        }

        $certificate = ORM::for_table('tbl_vpn_certificates')
            ->where('router_id', $router->id)
            ->where('status', 'active')
            ->order_by_desc('issued_date')
            ->find_one();

        return $certificate;
    }

    /**
     * Retrieve recent connection logs with limit parameter
     * 
     * @param object $router ORM instance of router
     * @param int $limit Maximum number of logs to retrieve (default: 10)
     * @return array Array of connection log records
     */
    public static function getConnectionLogs($router, $limit = 10)
    {
        if (!isset($router->id)) {
            return [];
        }

        $logs = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $router->id)
            ->order_by_desc('created_at')
            ->limit($limit)
            ->find_many();

        return $logs;
    }
}
