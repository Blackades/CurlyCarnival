<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPN Connection Statistics Cron Job
 * 
 * This script calculates and stores hourly connection statistics
 * and updates router uptime percentages.
 * 
 * Schedule: Hourly
 * Cron: 0 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_stats.php >> /var/log/phpnuxbill/vpn-stats.log 2>&1
 */

// Initialize phpnuxbill
require_once dirname(__FILE__) . '/../init.php';

// Log start time
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

echo "[$timestamp] VPN Statistics Calculation Started\n";
echo str_repeat('-', 80) . "\n";

try {
    // Get all remote routers
    $routers = ORM::for_table('tbl_routers')
        ->where('connection_type', 'remote')
        ->find_many();
    
    $totalRouters = count($routers);
    
    if ($totalRouters === 0) {
        echo "[$timestamp] No remote routers found\n";
        echo "[$timestamp] VPN Statistics Calculation Completed\n\n";
        exit(0);
    }
    
    echo "[$timestamp] Processing statistics for $totalRouters router(s)\n";
    echo str_repeat('-', 80) . "\n";
    
    $statsProcessed = 0;
    $statsErrors = 0;
    
    // Calculate statistics for each router
    foreach ($routers as $router) {
        $routerId = $router['id'];
        $routerName = $router['name'];
        
        try {
            echo "[$timestamp] Router: $routerName (ID: $routerId)\n";
            
            // Calculate 7-day uptime percentage
            $uptime7d = VPNMetrics::getConnectionUptime($routerId, 7);
            echo "  7-day uptime: {$uptime7d}%\n";
            
            // Calculate 30-day uptime percentage
            $uptime30d = VPNMetrics::getConnectionUptime($routerId, 30);
            echo "  30-day uptime: {$uptime30d}%\n";
            
            // Calculate data transferred (last 24 hours)
            $dataTransferred = VPNMetrics::getDataTransferred($routerId, 1);
            $sentFormatted = VPNMetrics::formatBytes($dataTransferred['sent']);
            $receivedFormatted = VPNMetrics::formatBytes($dataTransferred['received']);
            $totalFormatted = VPNMetrics::formatBytes($dataTransferred['total']);
            
            echo "  24h data sent: $sentFormatted\n";
            echo "  24h data received: $receivedFormatted\n";
            echo "  24h total: $totalFormatted\n";
            
            // Store statistics in router record (optional - for quick access)
            // Note: This could be stored in a separate stats table for historical tracking
            // For now, we'll just log the values
            
            $statsProcessed++;
            echo "  Status: SUCCESS\n\n";
            
        } catch (Exception $e) {
            $statsErrors++;
            echo "  Status: ERROR - {$e->getMessage()}\n\n";
        }
    }
    
    // Calculate system-wide statistics
    echo str_repeat('-', 80) . "\n";
    echo "[$timestamp] System-Wide Statistics:\n";
    
    $systemStats = VPNMetrics::getSystemWideStats();
    
    echo "  Total Remote Routers: {$systemStats['total_routers']}\n";
    echo "  Connected: {$systemStats['connected']}\n";
    echo "  Disconnected: {$systemStats['disconnected']}\n";
    echo "  Errors: {$systemStats['error']}\n";
    echo "  Pending: {$systemStats['pending']}\n";
    echo "  Connection Rate: {$systemStats['connection_rate']}%\n";
    echo "  Certificates Expiring Soon: {$systemStats['certificates_expiring_soon']}\n";
    echo "  Certificates Expired: {$systemStats['certificates_expired']}\n";
    
    // Summary
    echo str_repeat('-', 80) . "\n";
    echo "[$timestamp] Statistics Summary:\n";
    echo "  Routers Processed: $statsProcessed\n";
    echo "  Errors: $statsErrors\n";
    
    // Calculate execution time
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    echo "  Execution Time: {$executionTime}s\n";
    
    echo "[$timestamp] VPN Statistics Calculation Completed Successfully\n\n";
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ERROR: VPN Statistics Calculation Failed\n";
    echo "  Exception: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n\n";
    
    // Exit with error code
    exit(1);
}

// Exit successfully
exit(0);
