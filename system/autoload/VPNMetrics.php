<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNMetrics class
 * Calculates VPN connection metrics and statistics
 */
class VPNMetrics
{
    /**
     * Get connection uptime percentage
     *
     * @param int $routerId
     * @param int $days Number of days to calculate
     * @return float Uptime percentage
     */
    public static function getConnectionUptime($routerId, $days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $logs = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $routerId)
            ->where_gte('created_at', $startDate)
            ->order_by_desc('created_at')
            ->find_many();
        
        if (empty($logs)) {
            return 0.0;
        }

        $totalSeconds = $days * 24 * 60 * 60;
        $connectedSeconds = 0;
        
        foreach ($logs as $log) {
            if ($log['connection_status'] === 'connected' && 
                $log['connection_time'] && 
                $log['disconnection_time']) {
                
                $start = strtotime($log['connection_time']);
                $end = strtotime($log['disconnection_time']);
                $connectedSeconds += ($end - $start);
            }
        }

        $uptime = ($connectedSeconds / $totalSeconds) * 100;
        return round($uptime, 2);
    }

    /**
     * Get data transferred (bytes sent and received)
     *
     * @param int $routerId
     * @param int $days Number of days to calculate
     * @return array ['sent' => bytes, 'received' => bytes, 'total' => bytes]
     */
    public static function getDataTransferred($routerId, $days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $result = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $routerId)
            ->where_gte('created_at', $startDate)
            ->select_expr('SUM(bytes_sent)', 'total_sent')
            ->select_expr('SUM(bytes_received)', 'total_received')
            ->find_one();
        
        $sent = $result ? (int)$result['total_sent'] : 0;
        $received = $result ? (int)$result['total_received'] : 0;
        
        return [
            'sent' => $sent,
            'received' => $received,
            'total' => $sent + $received
        ];
    }

    /**
     * Get system-wide statistics for all remote routers
     *
     * @return array
     */
    public static function getSystemWideStats()
    {
        $routers = ORM::for_table('tbl_routers')
            ->where('connection_type', 'remote')
            ->find_many();
        
        $stats = [
            'total_routers' => count($routers),
            'connected' => 0,
            'disconnected' => 0,
            'error' => 0,
            'pending' => 0,
            'connection_rate' => 0.0,
            'certificates_expiring_soon' => 0,
            'certificates_expired' => 0
        ];

        foreach ($routers as $router) {
            switch ($router['ovpn_status']) {
                case 'connected':
                    $stats['connected']++;
                    break;
                case 'disconnected':
                    $stats['disconnected']++;
                    break;
                case 'error':
                    $stats['error']++;
                    break;
                case 'pending':
                    $stats['pending']++;
                    break;
            }
        }

        if ($stats['total_routers'] > 0) {
            $stats['connection_rate'] = round(
                ($stats['connected'] / $stats['total_routers']) * 100, 
                2
            );
        }

        // Count expiring certificates (within 30 days)
        $expiryDate = date('Y-m-d', strtotime('+30 days'));
        $stats['certificates_expiring_soon'] = ORM::for_table('tbl_vpn_certificates')
            ->where('status', 'active')
            ->where_lte('expiry_date', $expiryDate)
            ->where_gt('expiry_date', date('Y-m-d'))
            ->count();

        // Count expired certificates
        $stats['certificates_expired'] = ORM::for_table('tbl_vpn_certificates')
            ->where('status', 'expired')
            ->count();

        return $stats;
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
