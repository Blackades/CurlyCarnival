<?php

/**
 * VPNConnectionLog Model - ORM model for tbl_vpn_connection_logs
 * Manages connection history and monitoring data
 */

class VPNConnectionLog
{
    /**
     * Insert connection log record
     * 
     * @param int $routerId Router ID
     * @param string $status Connection status ('connected', 'disconnected', 'error')
     * @param array $details Additional connection details
     *   - vpn_ip: VPN IP address
     *   - bytes_sent: Data sent in bytes
     *   - bytes_received: Data received in bytes
     *   - connection_time: Connection start time
     *   - disconnection_time: Connection end time
     *   - error_details: Error information
     * @return object Created connection log record
     */
    public static function logConnection($routerId, $status, $details = [])
    {
        $log = ORM::for_table('tbl_vpn_connection_logs')->create();
        $log->router_id = $routerId;
        $log->connection_status = $status;
        
        // Set optional fields from details array
        $log->vpn_ip = isset($details['vpn_ip']) ? $details['vpn_ip'] : null;
        $log->bytes_sent = isset($details['bytes_sent']) ? $details['bytes_sent'] : 0;
        $log->bytes_received = isset($details['bytes_received']) ? $details['bytes_received'] : 0;
        $log->connection_time = isset($details['connection_time']) ? $details['connection_time'] : null;
        $log->disconnection_time = isset($details['disconnection_time']) ? $details['disconnection_time'] : null;
        $log->error_details = isset($details['error_details']) ? $details['error_details'] : null;
        
        $log->save();
        
        return $log;
    }

    /**
     * Retrieve connection history for specified days
     * 
     * @param int $routerId Router ID
     * @param int $days Number of days of history to retrieve (default: 7)
     * @return array Array of connection log records
     */
    public static function getRouterHistory($routerId, $days = 7)
    {
        $cutoffDate = new DateTime();
        $cutoffDate->modify("-{$days} days");
        
        $logs = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $routerId)
            ->where_gte('created_at', $cutoffDate->format('Y-m-d H:i:s'))
            ->order_by_desc('created_at')
            ->find_many();

        return $logs;
    }
}
