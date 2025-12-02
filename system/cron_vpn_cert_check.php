<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPN Certificate Expiry Check Cron Job
 * 
 * This script checks for expiring VPN certificates and sends email alerts
 * at configured intervals (typically 30, 14, and 7 days before expiry).
 * 
 * Schedule: Daily at 2 AM
 * Cron: 0 2 * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_cert_check.php >> /var/log/phpnuxbill/vpn-cert-check.log 2>&1
 */

// Initialize phpnuxbill
require_once dirname(__FILE__) . '/../init.php';

// Log start time
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

echo "[$timestamp] VPN Certificate Expiry Check Started\n";
echo str_repeat('-', 80) . "\n";

try {
    // Initialize monitoring service
    $monitor = new VPNMonitoringService();
    
    // Check certificate expirations
    $expiringCerts = $monitor->checkCertificateExpirations();
    
    // Process results
    $totalExpiring = count($expiringCerts);
    $alertsSent = 0;
    $alertsFailed = 0;
    
    if ($totalExpiring === 0) {
        echo "[$timestamp] No expiring certificates found\n";
    } else {
        echo "[$timestamp] Found $totalExpiring expiring certificate(s)\n";
        echo str_repeat('-', 80) . "\n";
        
        foreach ($expiringCerts as $cert) {
            $routerId = $cert['router_id'];
            $daysRemaining = $cert['days_remaining'];
            $expiryDate = $cert['expiry_date'];
            
            // Get router details
            $router = ORM::for_table('tbl_routers')->find_one($routerId);
            
            if (!$router) {
                echo "[$timestamp] Router ID $routerId: NOT FOUND (skipping)\n";
                continue;
            }
            
            echo "[$timestamp] Router: {$router['name']} (ID: $routerId)\n";
            echo "  Certificate expires in: $daysRemaining days\n";
            echo "  Expiry date: $expiryDate\n";
            
            try {
                // Send alert email
                $monitor->sendCertificateExpiryAlert($routerId, $daysRemaining);
                $alertsSent++;
                echo "  Alert sent: SUCCESS\n";
            } catch (Exception $e) {
                $alertsFailed++;
                echo "  Alert sent: FAILED - {$e->getMessage()}\n";
            }
            
            echo "\n";
        }
    }
    
    // Check for already expired certificates
    $expiredCerts = ORM::for_table('tbl_vpn_certificates')
        ->where('status', 'active')
        ->where_lt('expiry_date', date('Y-m-d'))
        ->find_many();
    
    if (count($expiredCerts) > 0) {
        echo str_repeat('-', 80) . "\n";
        echo "[$timestamp] Found " . count($expiredCerts) . " expired certificate(s)\n";
        
        foreach ($expiredCerts as $cert) {
            // Update certificate status to expired
            $cert->status = 'expired';
            $cert->save();
            
            $router = ORM::for_table('tbl_routers')->find_one($cert['router_id']);
            $routerName = $router ? $router['name'] : "Unknown (ID: {$cert['router_id']})";
            
            echo "  Router: $routerName - Certificate marked as EXPIRED\n";
        }
    }
    
    // Summary
    echo str_repeat('-', 80) . "\n";
    echo "[$timestamp] Certificate Check Summary:\n";
    echo "  Expiring Certificates: $totalExpiring\n";
    echo "  Alerts Sent: $alertsSent\n";
    echo "  Alerts Failed: $alertsFailed\n";
    echo "  Expired Certificates Updated: " . count($expiredCerts) . "\n";
    
    // Calculate execution time
    $endTime = microtime(true);
    $executionTime = round($endTime - $startTime, 2);
    echo "  Execution Time: {$executionTime}s\n";
    
    echo "[$timestamp] VPN Certificate Check Completed Successfully\n\n";
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ERROR: VPN Certificate Check Failed\n";
    echo "  Exception: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "  Trace:\n" . $e->getTraceAsString() . "\n\n";
    
    // Exit with error code
    exit(1);
}

// Exit successfully
exit(0);
