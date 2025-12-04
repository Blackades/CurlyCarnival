<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";

// Check if HTTP_HOST is set, otherwise use a default value or SERVER_NAME
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');

$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('APP_URL', $protocol . $host . $baseDir);


$_app_stage = 'Live'; # Do not change this

$db_host    = "localhost"; # Database Host
$db_port    = "";   # Database Port. Keep it blank if you are un sure.
$db_user    = "root"; # Database Username
$db_pass    = ""; # Database Password
$db_name    = "phpnuxbill"; # Database Name




//error reporting
if($_app_stage!='Live'){
    error_reporting(E_ERROR);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}else{
    error_reporting(E_ERROR);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// ============================================================================
// VPN Configuration for Remote Router Management
// ============================================================================

// Enable/Disable VPN functionality
$config['vpn_enabled'] = true;

// OpenVPN Server Configuration
$config['vpn_server_ip'] = '203.0.113.10';      // Public VPS IP address
$config['vpn_server_port'] = 1194;              // OpenVPN server port
$config['vpn_protocol'] = 'tcp';                // Protocol: tcp or udp
$config['vpn_subnet'] = '10.8.0.0/24';          // VPN subnet for client IPs

// Security Configuration
$config['vpn_encryption_key'] = 'CHANGE-THIS-TO-A-RANDOM-32-CHARACTER-KEY!!';  // For password encryption (must be 32 characters)

// Certificate Management (EasyRSA)
$config['easyrsa_dir'] = '/etc/openvpn/easy-rsa';           // EasyRSA installation directory
$config['vpn_cert_validity_days'] = 3650;                    // Certificate validity period in days
$config['vpn_cert_warning_days'] = 30;                      // Days before expiry to show warning

// Storage Paths
$config['vpn_storage_dir'] = '/var/www/html/system/storage/vpn-configs';  // VPN config storage directory
$config['vpn_script_dir'] = '/var/www/html/system/scripts/vpn';           // VPN management scripts directory

// Monitoring Configuration
$config['vpn_health_check_interval'] = 300;     // Health check interval in seconds (5 minutes)
$config['vpn_connection_timeout'] = 10;         // Connection timeout in seconds
$config['vpn_max_retry_attempts'] = 3;          // Maximum retry attempts before marking as failed

// Alert Configuration
$config['vpn_alert_email'] = 'admin@example.com';           // Email address for VPN alerts
$config['vpn_alert_on_disconnect'] = true;                  // Send alert when router disconnects
$config['vpn_alert_cert_expiry_days'] = [30, 14, 7];       // Send alerts at these days before certificate expiry

// ============================================================================
// End of VPN Configuration
// ============================================================================
