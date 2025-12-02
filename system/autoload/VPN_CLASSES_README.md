# VPN Service Classes Documentation

This document provides an overview of the VPN service classes implemented for the MikroTik OpenVPN automation feature.

## Created Classes

### 1. VPNException.php
Custom exception class for VPN-related errors with error codes and context.

**Error Code Ranges:**
- 1000-1999: Validation errors
- 2000-2999: Certificate errors
- 3000-3999: Script execution errors
- 4000-4999: Network errors
- 5000-5999: File system errors
- 6000-6999: Database errors

### 2. VPNInputValidator.php
Validates VPN-related user inputs.

**Methods:**
- `validateUsername($username)` - Validates username format (3-32 alphanumeric, underscore, hyphen)
- `validatePassword($password)` - Validates password strength (min 8 chars, uppercase, lowercase, number)
- `validateIPAddress($ip)` - Validates IP address format
- `sanitizeShellArg($arg)` - Sanitizes shell arguments to prevent injection

### 3. VPNPasswordManager.php
Handles password hashing and encryption.

**Methods:**
- `hashPassword($password)` - Hash using Argon2ID for database storage
- `verifyPassword($password, $hash)` - Verify password against hash
- `encryptForStorage($password, $key)` - Encrypt using AES-256-CBC
- `decryptFromStorage($encrypted, $key)` - Decrypt from storage

### 4. CertificateManager.php
Manages OpenVPN certificates using EasyRSA.

**Methods:**
- `checkCertificateValidity()` - Check CA and server certificate validity
- `generateClientCertificate($clientName, $validityDays)` - Generate client certificate
- `revokeCertificate($clientName)` - Revoke certificate
- `getCertificateExpiryDate($certPath)` - Get certificate expiry date
- `getCertificatesExpiringSoon($days)` - Query expiring certificates
- `cleanupExpiredCertificates()` - Remove expired certificate files

### 5. OpenVPNService.php
Manages OpenVPN service and user authentication.

**Methods:**
- `addVPNUser($username, $password)` - Add VPN user to authentication script
- `removeVPNUser($username)` - Remove VPN user
- `updateVPNUser($username, $newPassword)` - Update user password
- `generateOVPNConfig($clientName, $serverIP, $serverPort, $certDir)` - Generate OVPN config file
- `restartService()` - Restart OpenVPN service
- `getServiceStatus()` - Get service status
- `getConnectedClients()` - Get list of connected clients
- `getClientConnectionInfo($vpnIP)` - Get specific client info

### 6. MikroTikScriptGenerator.php
Generates MikroTik RouterOS configuration scripts.

**Methods:**
- `generateRouterOSScript($config)` - Generate complete RouterOS setup script
- `generateSetupInstructions($config)` - Generate step-by-step instructions
- `createConfigurationPackage($routerId, $files)` - Create ZIP package with all files

### 7. VPNConfigurationService.php
Orchestrates the complete VPN configuration workflow.

**Methods:**
- `createRemoteRouterConfiguration($routerData)` - Complete router setup workflow
- `validateVPNCredentials($username, $password)` - Validate credentials
- `checkUsernameAvailability($username)` - Check if username is available
- `rollbackConfiguration($routerId)` - Rollback failed configuration
- `testVPNConnection($vpnIP, $apiUser, $apiPass, $apiPort)` - Test VPN and API connectivity
- `getNextAvailableVPNIP()` - Calculate next available VPN IP

### 8. VPNMonitoringService.php
Monitors VPN connections and certificate expiration.

**Methods:**
- `checkAllRouterConnections()` - Check all remote router connections
- `checkRouterConnection($routerId)` - Check single router connection
- `updateConnectionStatus($routerId, $status, $details)` - Update connection status
- `getRouterConnectionHistory($routerId, $days)` - Get connection history
- `sendDisconnectionAlert($routerId)` - Send disconnection alert
- `checkCertificateExpirations()` - Check for expiring certificates
- `sendCertificateExpiryAlert($routerId, $daysRemaining)` - Send expiry alert

### 9. VPNRollbackManager.php
Manages rollback operations for failed configurations.

**Methods:**
- `addRollbackAction($action, $params)` - Add action to rollback stack
- `executeRollback()` - Execute all rollback actions in reverse order
- `clearRollback()` - Clear rollback stack
- `getStackSize()` - Get current stack size

**Supported Rollback Actions:**
- removeVPNUser
- revokeCertificate
- restoreAuthScript
- deleteRouter
- deleteFiles
- deleteCertificateRecord

### 10. VPNAccessControl.php
Manages access control for VPN operations.

**Methods:**
- `canManageVPN($admin)` - Check if admin can manage VPN (SuperAdmin, Admin)
- `canViewVPNLogs($admin)` - Check if admin can view logs (SuperAdmin, Admin, Report)
- `canRenewCertificates($admin)` - Check if admin can renew certificates (SuperAdmin only)

### 11. VPNAlertManager.php
Manages VPN-related email alerts.

**Methods:**
- `sendDisconnectionAlert($routerId, $details)` - Send disconnection alert email
- `sendCertificateExpiryAlert($routerId, $daysRemaining)` - Send certificate expiry alert

### 12. VPNMetrics.php
Calculates VPN connection metrics and statistics.

**Methods:**
- `getConnectionUptime($routerId, $days)` - Calculate uptime percentage
- `getDataTransferred($routerId, $days)` - Get bytes sent/received
- `getSystemWideStats()` - Get system-wide statistics for all routers
- `formatBytes($bytes, $precision)` - Format bytes to human-readable format

## Class Dependencies

```
VPNConfigurationService
├── CertificateManager
│   └── VPNException
├── OpenVPNService
│   └── VPNException
├── MikroTikScriptGenerator
│   └── VPNException
├── VPNRollbackManager
│   ├── OpenVPNService
│   ├── CertificateManager
│   └── VPNException
├── VPNInputValidator
│   └── VPNException
└── VPNPasswordManager

VPNMonitoringService
├── VPNAlertManager
└── VPNMetrics

VPNAccessControl (standalone)
```

## Usage Example

```php
// Create remote router configuration
$vpnService = new VPNConfigurationService();

$routerData = [
    'name' => 'Branch Office Router',
    'vpn_username' => 'branch-office-01',
    'vpn_password' => 'SecurePass123',
    'api_username' => 'admin',
    'api_password' => 'admin123',
    'api_port' => 8728,
    'description' => 'Branch office in New York',
    'cert_validity_days' => 365
];

try {
    $result = $vpnService->createRemoteRouterConfiguration($routerData);
    
    echo "Router created successfully!\n";
    echo "Router ID: " . $result['router_id'] . "\n";
    echo "VPN IP: " . $result['vpn_ip'] . "\n";
    echo "Config Package: " . $result['config_package_path'] . "\n";
    
} catch (VPNException $e) {
    echo "Configuration failed: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getErrorCode() . "\n";
}
```

## Configuration Requirements

The following configuration values should be set in `config.php`:

```php
$config['vpn_enabled'] = true;
$config['vpn_server_ip'] = '203.0.113.10';
$config['vpn_server_port'] = 1194;
$config['vpn_protocol'] = 'tcp';
$config['vpn_subnet'] = '10.8.0.0/24';
$config['vpn_encryption_key'] = 'your-secret-key';
$config['easyrsa_dir'] = '/etc/openvpn/easy-rsa';
$config['vpn_cert_validity_days'] = 365;
$config['vpn_cert_warning_days'] = 30;
$config['vpn_storage_dir'] = '/var/www/html/system/storage/vpn-configs';
$config['vpn_script_dir'] = '/var/www/html/system/scripts/vpn';
$config['vpn_health_check_interval'] = 300;
$config['vpn_connection_timeout'] = 10;
$config['vpn_max_retry_attempts'] = 3;
$config['vpn_alert_email'] = 'admin@example.com';
$config['vpn_alert_on_disconnect'] = true;
$config['vpn_alert_cert_expiry_days'] = [30, 14, 7];
```

## Security Features

1. **Input Validation**: All user inputs are validated before processing
2. **Shell Argument Sanitization**: All shell arguments are escaped using `escapeshellarg()`
3. **Password Hashing**: Passwords are hashed using Argon2ID
4. **Encryption**: Sensitive data is encrypted using AES-256-CBC
5. **Access Control**: Role-based access control for VPN operations
6. **Audit Logging**: All VPN operations are logged with admin ID and IP address
7. **Rollback Mechanism**: Automatic rollback on configuration failures

## Error Handling

All classes throw `VPNException` on errors with appropriate error codes. The rollback mechanism ensures system consistency even when operations fail.

## Next Steps

After implementing these service classes, the next tasks are:
1. Create ORM model classes (Router, VPNAuditLog, VPNCertificate, VPNConnectionLog)
2. Extend routers controller with VPN actions
3. Create Smarty template files
4. Create cron job scripts
5. Create dashboard widgets
