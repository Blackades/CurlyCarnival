<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPN Health Check Cron Job
 * 
 * This script checks the VPN connection status of all remote routers
 * and sends alerts for disconnected or error states.
 * 
 * Schedule: Every 5 minutes
 * Cron: (star)/5 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_health.php >> /var/log/phpnuxbill/vpn-health.log 2>&1
 */

// Initialize phpnuxbill
require_once dirname(__FILE__) . '/../init.php';

// Log start time
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

echo "[$timestamp] VPN Health Check Started\n";
echo str_repeat('-', 80) . "\n";

try {
    // Initialize monitoring service
    $monitor = new VPNMonitoringService();
    
    // Check all router connections
    $results = $monitor->checkAllRouterConnections();
    
    // Process results
    $totalChecked = count($results);
    $connected = 0;
    $disconnected = 0;
    $errors = 0;
    $pending = 0;
    
    foreach ($results as $routerId => $result) {
        $status = $result['status'];
        
        switch ($status) {
            case 'connected':
                $connected++;
                echo "[$timestamp] Router ID $routerId: CONNECTED\n";
                break;
                
            case 'disconnected':
                $disconnected++;
                echo "[$timestamp] Router ID $routerId: DISCONNECTED - {$result['message']}\n";
                
                // Log to connection logs
                VPNConnectionLog::logConnection($routerId, 'disconnected', [
                    'error' => $result['message'],
                    'vpn_reachable' => $result['vpn_reachable'] ?? false
                ]);
                
                // Alert will be sent by VPNMonitoringService after 3 consecutive failures
                break;
                
            case 'error':
                $errors++;
                echo "[$timestamp] Router ID $routerId: ERROR - {$result['message']}\n";
                
                // Log to connection logs
                VPNConnectionLog::logConnection($routerId, 'error', [
                    'error' => $result['message'],
                    'vpn_reachable' => $result['vpn_reachable'] ?? false,
                    'api_accessible' => $result['api_accessible'] ?? false
                ]);
                break;
                
            case 'pending':
                $pending++;
                echo "[$timestamp] Router ID $routerId: PENDING - {$result['message']}\n";
                break;
        }
    }
    
    // Summary
    echo str_repeat('-', 80) . "\n";
    echo "[$timestamp] Health Check Summary:\n";
    echo "  Total Routers Checked: $totalChecked\n";
    echo "  Connected: $connected\n";
    echo "  Disconnected: $disconnected\n";
    echo "  Errors: $errors\n";
    echo "  Pending: $pending\n";
    
    // Calculate execution time
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    echo "  Execution Time: {$executionTime}s\n";
    
    echo "[$timestamp] VPN Health Check Completed Successfully\n\n";
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ERROR: VPN Health Check Failed\n";
    echo "  Exception: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n\n";
    
    // Exit with error code
    exit(1);
}

// Exit successfully
exit(0);
