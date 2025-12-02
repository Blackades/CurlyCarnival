# VPN ORM Models Documentation

This document describes the ORM model classes for VPN feature database operations.

## Overview

The VPN feature uses four model classes to interact with the database:
- **Router** - Extensions for tbl_routers table
- **VPNAuditLog** - Audit trail management
- **VPNCertificate** - Certificate lifecycle tracking
- **VPNConnectionLog** - Connection history and monitoring

All models use the Idiorm ORM pattern with static methods for common operations.

## Router Model

**File:** `system/autoload/Router.php`

Helper methods for router VPN operations.

### Methods

#### `Router::isRemote($router)`
Check if router requires VPN connection.

```php
$router = ORM::for_table('tbl_routers')->find_one($id);
if (Router::isRemote($router)) {
    // Handle remote router
}
```

**Returns:** `bool` - True if connection_type is 'remote'

#### `Router::getVPNStatus($router)`
Get current VPN connection status.

```php
$status = Router::getVPNStatus($router);
// Returns: 'connected', 'disconnected', 'error', or 'pending'
```

**Returns:** `string` - Current ovpn_status value

#### `Router::getCertificateInfo($router)`
Retrieve active certificate details.

```php
$cert = Router::getCertificateInfo($router);
if ($cert) {
    echo "Expires: " . $cert->expiry_date;
}
```

**Returns:** `object|false` - Certificate record or false if not found

#### `Router::getConnectionLogs($router, $limit = 10)`
Get recent connection logs.

```php
$logs = Router::getConnectionLogs($router, 20);
foreach ($logs as $log) {
    echo $log->connection_status . " at " . $log->created_at;
}
```

**Parameters:**
- `$router` - ORM router instance
- `$limit` - Maximum logs to retrieve (default: 10)

**Returns:** `array` - Array of connection log records

## VPNAuditLog Model

**File:** `system/autoload/VPNAuditLog.php`

Manages audit trail for VPN administrative actions.

### Methods

#### `VPNAuditLog::logAction($routerId, $action, $adminId, $details, $status, $errorMessage)`
Insert audit log record.

```php
VPNAuditLog::logAction(
    $routerId,
    'vpn_user_created',
    $admin['id'],
    ['vpn_username' => 'router-001'],
    'success'
);
```

**Parameters:**
- `$routerId` - Router ID
- `$action` - Action type (e.g., 'vpn_user_created', 'certificate_renewed')
- `$adminId` - Admin user ID
- `$details` - Array or string of action details (arrays are JSON encoded)
- `$status` - 'success', 'failed', or 'pending' (default: 'success')
- `$errorMessage` - Error message if failed (default: null)

**Returns:** `object` - Created audit log record

**Action Types:**
- `vpn_user_created` - VPN user added
- `vpn_user_updated` - VPN password changed
- `vpn_user_deleted` - VPN user removed
- `certificate_generated` - New certificate created
- `certificate_renewed` - Certificate renewed
- `certificate_revoked` - Certificate revoked
- `config_downloaded` - Configuration downloaded
- `connection_tested` - Connection test performed
- `router_created` - Remote router created
- `router_updated` - Remote router updated
- `router_deleted` - Remote router deleted

#### `VPNAuditLog::getRouterLogs($routerId, $limit = 50)`
Retrieve audit logs for specific router.

```php
$logs = VPNAuditLog::getRouterLogs($routerId, 100);
foreach ($logs as $log) {
    echo $log->action . " by admin " . $log->admin_id;
}
```

**Parameters:**
- `$routerId` - Router ID
- `$limit` - Maximum logs to retrieve (default: 50)

**Returns:** `array` - Array of audit log records

## VPNCertificate Model

**File:** `system/autoload/VPNCertificate.php`

Certificate lifecycle and expiry management.

### Methods

#### `VPNCertificate::isExpired($certificate)`
Check if certificate has expired.

```php
$cert = ORM::for_table('tbl_vpn_certificates')->find_one($id);
if (VPNCertificate::isExpired($cert)) {
    echo "Certificate expired!";
}
```

**Returns:** `bool` - True if expiry date has passed

#### `VPNCertificate::isExpiringSoon($certificate, $days = 30)`
Check if certificate expires within specified days.

```php
if (VPNCertificate::isExpiringSoon($cert, 14)) {
    echo "Certificate expires in less than 14 days";
}
```

**Parameters:**
- `$certificate` - ORM certificate instance
- `$days` - Days threshold (default: 30)

**Returns:** `bool` - True if expires within specified days

#### `VPNCertificate::getDaysUntilExpiry($certificate)`
Calculate days remaining until expiry.

```php
$days = VPNCertificate::getDaysUntilExpiry($cert);
if ($days < 0) {
    echo "Expired " . abs($days) . " days ago";
} else {
    echo "Expires in " . $days . " days";
}
```

**Returns:** `int` - Days until expiry (negative if expired)

#### `VPNCertificate::getExpiringSoon($days = 30)`
Query all certificates expiring within specified days.

```php
$expiring = VPNCertificate::getExpiringSoon(30);
foreach ($expiring as $cert) {
    $router = ORM::for_table('tbl_routers')->find_one($cert->router_id);
    echo $router->name . " certificate expires " . $cert->expiry_date;
}
```

**Parameters:**
- `$days` - Days threshold (default: 30)

**Returns:** `array` - Array of certificate records expiring soon

## VPNConnectionLog Model

**File:** `system/autoload/VPNConnectionLog.php`

Connection history and monitoring data.

### Methods

#### `VPNConnectionLog::logConnection($routerId, $status, $details)`
Insert connection log record.

```php
VPNConnectionLog::logConnection($routerId, 'connected', [
    'vpn_ip' => '10.8.0.2',
    'bytes_sent' => 1024000,
    'bytes_received' => 2048000,
    'connection_time' => date('Y-m-d H:i:s')
]);
```

**Parameters:**
- `$routerId` - Router ID
- `$status` - Connection status ('connected', 'disconnected', 'error')
- `$details` - Array with optional fields:
  - `vpn_ip` - VPN IP address
  - `bytes_sent` - Data sent in bytes
  - `bytes_received` - Data received in bytes
  - `connection_time` - Connection start time
  - `disconnection_time` - Connection end time
  - `error_details` - Error information

**Returns:** `object` - Created connection log record

#### `VPNConnectionLog::getRouterHistory($routerId, $days = 7)`
Retrieve connection history for specified days.

```php
$history = VPNConnectionLog::getRouterHistory($routerId, 30);
foreach ($history as $log) {
    echo $log->connection_status . " at " . $log->created_at;
}
```

**Parameters:**
- `$routerId` - Router ID
- `$days` - Days of history (default: 7)

**Returns:** `array` - Array of connection log records

## Usage Examples

### Complete Router Setup Workflow

```php
// Create remote router
$router = ORM::for_table('tbl_routers')->create();
$router->name = 'branch-office-1';
$router->connection_type = 'remote';
$router->vpn_username = 'vpn-branch-1';
$router->ovpn_status = 'pending';
$router->save();

// Log the creation
VPNAuditLog::logAction(
    $router->id(),
    'router_created',
    $admin['id'],
    ['router_name' => $router->name],
    'success'
);

// Log initial connection attempt
VPNConnectionLog::logConnection($router->id(), 'pending', [
    'vpn_ip' => '10.8.0.2'
]);
```

### Certificate Expiry Check

```php
// Get all expiring certificates
$expiring = VPNCertificate::getExpiringSoon(30);

foreach ($expiring as $cert) {
    $router = ORM::for_table('tbl_routers')->find_one($cert->router_id);
    $days = VPNCertificate::getDaysUntilExpiry($cert);
    
    echo "Router: " . $router->name . "\n";
    echo "Expires in: " . $days . " days\n";
    
    // Send alert if critical
    if ($days <= 7) {
        // Send email alert
    }
}
```

### Connection Status Dashboard

```php
// Get all remote routers
$routers = ORM::for_table('tbl_routers')
    ->where('connection_type', 'remote')
    ->find_many();

foreach ($routers as $router) {
    $status = Router::getVPNStatus($router);
    $cert = Router::getCertificateInfo($router);
    $recentLogs = Router::getConnectionLogs($router, 5);
    
    echo "Router: " . $router->name . "\n";
    echo "Status: " . $status . "\n";
    
    if ($cert) {
        $days = VPNCertificate::getDaysUntilExpiry($cert);
        echo "Certificate expires in: " . $days . " days\n";
    }
    
    echo "Recent connections:\n";
    foreach ($recentLogs as $log) {
        echo "  - " . $log->connection_status . " at " . $log->created_at . "\n";
    }
}
```

### Audit Trail Review

```php
// Get audit logs for specific router
$logs = VPNAuditLog::getRouterLogs($routerId, 100);

foreach ($logs as $log) {
    $admin = ORM::for_table('tbl_users')->find_one($log->admin_id);
    $details = json_decode($log->details, true);
    
    echo $log->created_at . " - ";
    echo $log->action . " by " . $admin->username;
    echo " (" . $log->status . ")\n";
    
    if ($log->status === 'failed') {
        echo "Error: " . $log->error_message . "\n";
    }
}
```

## Database Tables

### tbl_routers (Extended)
- `connection_type` - 'local' or 'remote'
- `vpn_username` - OpenVPN username
- `vpn_password_hash` - Hashed password
- `vpn_ip` - Assigned VPN IP
- `certificate_path` - Certificate directory
- `certificate_expiry` - Expiry date
- `ovpn_status` - Connection status
- `last_vpn_check` - Last check timestamp
- `config_package_path` - ZIP package path

### tbl_vpn_audit_log
- `router_id` - Foreign key to tbl_routers
- `action` - Action type
- `admin_id` - Foreign key to tbl_users
- `details` - JSON encoded details
- `ip_address` - Admin IP
- `status` - success/failed/pending
- `error_message` - Error details
- `created_at` - Timestamp

### tbl_vpn_certificates
- `router_id` - Foreign key to tbl_routers
- `client_name` - Certificate CN
- `certificate_path` - .crt file path
- `key_path` - .key file path
- `ca_path` - ca.crt path
- `ovpn_file_path` - .ovpn file path
- `issued_date` - Issue date
- `expiry_date` - Expiry date
- `status` - active/expired/revoked
- `created_at` - Timestamp

### tbl_vpn_connection_logs
- `router_id` - Foreign key to tbl_routers
- `vpn_ip` - VPN IP address
- `connection_status` - connected/disconnected/error
- `bytes_sent` - Data sent
- `bytes_received` - Data received
- `connection_time` - Connection start
- `disconnection_time` - Connection end
- `error_details` - Error info
- `created_at` - Timestamp

## Best Practices

1. **Always log actions** - Use VPNAuditLog for all administrative operations
2. **Check certificate expiry** - Regularly monitor with VPNCertificate::getExpiringSoon()
3. **Monitor connections** - Log status changes with VPNConnectionLog
4. **Use transactions** - Wrap multiple operations in try-catch blocks
5. **Clean old logs** - Periodically archive/delete old connection logs

## Error Handling

```php
try {
    // Create router
    $router = ORM::for_table('tbl_routers')->create();
    $router->name = 'test-router';
    $router->connection_type = 'remote';
    $router->save();
    
    // Log success
    VPNAuditLog::logAction(
        $router->id(),
        'router_created',
        $admin['id'],
        ['router_name' => $router->name],
        'success'
    );
    
} catch (Exception $e) {
    // Log failure
    if (isset($router) && $router->id()) {
        VPNAuditLog::logAction(
            $router->id(),
            'router_created',
            $admin['id'],
            ['router_name' => $router->name],
            'failed',
            $e->getMessage()
        );
    }
    
    throw $e;
}
```

---

**Version:** 1.0  
**Last Updated:** December 3, 2025  
**Related:** VPN_CLASSES_README.md, VPN_SCHEMA_REFERENCE.md
