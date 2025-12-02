<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNMonitoringService class
 * Monitors VPN connections and certificate expiration
 */
class VPNMonitoringService
{
    private $connectionTimeout;
    private $maxRetryAttempts;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;
        
        $this->connectionTimeout = $config['vpn_connection_timeout'] ?? 10;
        $this->maxRetryAttempts = $config['vpn_max_retry_attempts'] ?? 3;
    }

    /**
     * Check all router connections
     *
     * @return array Results for each router
     */
    public function checkAllRouterConnections()
    {
        $routers = ORM::for_table('tbl_routers')
            ->where('connection_type', 'remote')
            ->find_many();
        
        $results = [];
        
        foreach ($routers as $router) {
            $result = $this->checkRouterConnection($router['id']);
            $results[$router['id']] = $result;
            
            // Send alert if disconnected for multiple checks
            if ($result['status'] === 'disconnected' || $result['status'] === 'error') {
                $this->handleDisconnection($router['id']);
            }
        }
        
        return $results;
    }

    /**
     * Check single router connection
     *
     * @param int $routerId
     * @return array Connection status
     */
    public function checkRouterConnection($routerId)
    {
        $router = ORM::for_table('tbl_routers')->find_one($routerId);
        
        if (!$router || $router['connection_type'] !== 'remote') {
            return [
                'status' => 'error',
                'message' => 'Router not found or not remote type'
            ];
        }

        if (empty($router['vpn_ip'])) {
            return [
                'status' => 'pending',
                'message' => 'VPN IP not configured'
            ];
        }

        // Test 1: Ping VPN IP
        $pingResult = $this->pingHost($router['vpn_ip']);
        
        if (!$pingResult['success']) {
            $this->updateConnectionStatus($routerId, 'disconnected', [
                'error' => 'VPN IP unreachable',
                'details' => $pingResult['message']
            ]);
            
            return [
                'status' => 'disconnected',
                'message' => 'VPN IP unreachable',
                'vpn_reachable' => false
            ];
        }

        // Test 2: Check API port accessibility
        $apiPort = $router['port'] ?? 8728;
        $portResult = $this->checkPort($router['vpn_ip'], $apiPort);
        
        if (!$portResult['success']) {
            $this->updateConnectionStatus($routerId, 'error', [
                'error' => 'API port not accessible',
                'details' => $portResult['message']
            ]);
            
            return [
                'status' => 'error',
                'message' => 'API port not accessible',
                'vpn_reachable' => true,
                'api_accessible' => false
            ];
        }

        // Connection successful
        $this->updateConnectionStatus($routerId, 'connected', [
            'vpn_ip' => $router['vpn_ip']
        ]);
        
        return [
            'status' => 'connected',
            'message' => 'Connection successful',
            'vpn_reachable' => true,
            'api_accessible' => true
        ];
    }

    /**
     * Update connection status in database
     *
     * @param int $routerId
     * @param string $status
     * @param array $details
     * @return void
     */
    public function updateConnectionStatus($routerId, $status, $details = [])
    {
        $router = ORM::for_table('tbl_routers')->find_one($routerId);
        
        if (!$router) {
            return;
        }

        $router->ovpn_status = $status;
        $router->last_vpn_check = date('Y-m-d H:i:s');
        $router->save();

        // Log connection event
        $log = ORM::for_table('tbl_vpn_connection_logs')->create();
        $log->router_id = $routerId;
        $log->vpn_ip = $details['vpn_ip'] ?? $router['vpn_ip'];
        $log->connection_status = $status;
        $log->error_details = isset($details['error']) ? json_encode($details) : null;
        $log->created_at = date('Y-m-d H:i:s');
        $log->save();
    }

    /**
     * Get router connection history
     *
     * @param int $routerId
     * @param int $days
     * @return array
     */
    public function getRouterConnectionHistory($routerId, $days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $logs = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $routerId)
            ->where_gte('created_at', $startDate)
            ->order_by_desc('created_at')
            ->find_many();
        
        return $logs;
    }

    /**
     * Handle disconnection - check if alert should be sent
     *
     * @param int $routerId
     * @return void
     */
    private function handleDisconnection($routerId)
    {
        // Get last 3 connection checks
        $recentLogs = ORM::for_table('tbl_vpn_connection_logs')
            ->where('router_id', $routerId)
            ->order_by_desc('created_at')
            ->limit(3)
            ->find_many();
        
        if (count($recentLogs) < 3) {
            return;
        }

        // Check if all last 3 checks failed
        $allFailed = true;
        foreach ($recentLogs as $log) {
            if ($log['connection_status'] === 'connected') {
                $allFailed = false;
                break;
            }
        }

        if ($allFailed) {
            $this->sendDisconnectionAlert($routerId);
        }
    }

    /**
     * Send disconnection alert
     *
     * @param int $routerId
     * @return void
     */
    public function sendDisconnectionAlert($routerId)
    {
        $router = ORM::for_table('tbl_routers')->find_one($routerId);
        
        if (!$router) {
            return;
        }

        VPNAlertManager::sendDisconnectionAlert($routerId, [
            'router_name' => $router['name'],
            'vpn_ip' => $router['vpn_ip'],
            'status' => $router['ovpn_status']
        ]);
    }

    /**
     * Check certificate expirations
     *
     * @return array Expiring certificates
     */
    public function checkCertificateExpirations()
    {
        global $config;
        
        $alertDays = $config['vpn_alert_cert_expiry_days'] ?? [30, 14, 7];
        $results = [];
        
        foreach ($alertDays as $days) {
            $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
            $nextDay = date('Y-m-d', strtotime("+{$days} days +1 day"));
            
            $certs = ORM::for_table('tbl_vpn_certificates')
                ->where('status', 'active')
                ->where_lte('expiry_date', $expiryDate)
                ->where_gt('expiry_date', date('Y-m-d'))
                ->find_many();
            
            foreach ($certs as $cert) {
                $this->sendCertificateExpiryAlert($cert['router_id'], $days);
                $results[] = [
                    'router_id' => $cert['router_id'],
                    'days_remaining' => $days,
                    'expiry_date' => $cert['expiry_date']
                ];
            }
        }
        
        return $results;
    }

    /**
     * Send certificate expiry alert
     *
     * @param int $routerId
     * @param int $daysRemaining
     * @return void
     */
    public function sendCertificateExpiryAlert($routerId, $daysRemaining)
    {
        VPNAlertManager::sendCertificateExpiryAlert($routerId, $daysRemaining);
    }

    /**
     * Ping host to check reachability
     *
     * @param string $host
     * @return array
     */
    private function pingHost($host)
    {
        // Use platform-specific ping command
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "ping -n 1 -w " . ($this->connectionTimeout * 1000) . " " . escapeshellarg($host);
        } else {
            $command = "ping -c 1 -W " . $this->connectionTimeout . " " . escapeshellarg($host);
        }
        
        exec($command . " 2>&1", $output, $exitCode);
        
        return [
            'success' => ($exitCode === 0),
            'message' => implode("\n", $output)
        ];
    }

    /**
     * Check if port is accessible
     *
     * @param string $host
     * @param int $port
     * @return array
     */
    private function checkPort($host, $port)
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, $this->connectionTimeout);
        
        if (is_resource($connection)) {
            fclose($connection);
            return [
                'success' => true,
                'message' => 'Port is accessible'
            ];
        }
        
        return [
            'success' => false,
            'message' => "Port not accessible: {$errstr} ({$errno})"
        ];
    }
}
