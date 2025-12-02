# VPN Configuration Reference

This document explains all VPN-related configuration settings in `config.php`.

## Overview

The VPN configuration settings enable phpnuxbill to automatically manage OpenVPN authentication and MikroTik router setup for secure remote router management. These settings must be configured before using the remote router functionality.

## Configuration Settings

### Basic Settings

#### `vpn_enabled`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Master switch to enable/disable VPN functionality
- **Requirements**: 1.1
- **Example**: `$config['vpn_enabled'] = true;`

### OpenVPN Server Configuration

#### `vpn_server_ip`
- **Type**: String (IP Address)
- **Default**: `'203.0.113.10'`
- **Description**: Public IP address of your VPS where OpenVPN server is running
- **Requirements**: 5.1
- **Important**: This must be the public IP that MikroTik routers will connect to
- **Example**: `$config['vpn_server_ip'] = '203.0.113.10';`

#### `vpn_server_port`
- **Type**: Integer
- **Default**: `1194`
- **Description**: Port number where OpenVPN server listens
- **Requirements**: 5.1
- **Example**: `$config['vpn_server_port'] = 1194;`

#### `vpn_protocol`
- **Type**: String
- **Default**: `'tcp'`
- **Description**: Protocol used by OpenVPN server
- **Requirements**: 5.1
- **Valid Values**: `'tcp'` or `'udp'`
- **Recommendation**: Use TCP for better reliability through firewalls
- **Example**: `$config['vpn_protocol'] = 'tcp';`

#### `vpn_subnet`
- **Type**: String (CIDR notation)
- **Default**: `'10.8.0.0/24'`
- **Description**: VPN subnet for assigning IP addresses to connected clients
- **Requirements**: 5.1
- **Example**: `$config['vpn_subnet'] = '10.8.0.0/24';`

### Security Configuration

#### `vpn_encryption_key`
- **Type**: String
- **Default**: `'CHANGE-THIS-TO-A-RANDOM-32-CHARACTER-KEY!!'`
- **Description**: Encryption key for storing VPN passwords securely
- **Requirements**: 6.1
- **Important**: 
  - MUST be changed from default value
  - Should be a random 32-character string
  - Keep this secret and secure
  - Changing this will invalidate existing encrypted passwords
- **Example**: `$config['vpn_encryption_key'] = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';`

### Certificate Management

#### `easyrsa_dir`
- **Type**: String (Directory Path)
- **Default**: `'/etc/openvpn/easy-rsa'`
- **Description**: Directory where EasyRSA is installed
- **Requirements**: 3.1
- **Example**: `$config['easyrsa_dir'] = '/etc/openvpn/easy-rsa';`

#### `vpn_cert_validity_days`
- **Type**: Integer
- **Default**: `365`
- **Description**: Number of days a client certificate remains valid
- **Requirements**: 3.1
- **Example**: `$config['vpn_cert_validity_days'] = 365;`

#### `vpn_cert_warning_days`
- **Type**: Integer
- **Default**: `30`
- **Description**: Number of days before expiry to start showing warnings
- **Requirements**: 3.1, 10.5
- **Example**: `$config['vpn_cert_warning_days'] = 30;`

### Storage Paths

#### `vpn_storage_dir`
- **Type**: String (Directory Path)
- **Default**: `'/var/www/html/system/storage/vpn-configs'`
- **Description**: Directory where VPN configuration packages are stored
- **Requirements**: 6.1
- **Important**: 
  - Directory must be writable by web server user
  - Should be outside web root for security
  - Will be created automatically by install script
- **Example**: `$config['vpn_storage_dir'] = '/var/www/html/system/storage/vpn-configs';`

#### `vpn_script_dir`
- **Type**: String (Directory Path)
- **Default**: `'/var/www/html/system/scripts/vpn'`
- **Description**: Directory containing VPN management bash scripts
- **Requirements**: 6.1
- **Important**: Scripts must have proper permissions (750) and ownership (root:www-data)
- **Example**: `$config['vpn_script_dir'] = '/var/www/html/system/scripts/vpn';`

### Monitoring Configuration

#### `vpn_health_check_interval`
- **Type**: Integer (seconds)
- **Default**: `300` (5 minutes)
- **Description**: How often to check VPN connection status for all remote routers
- **Requirements**: 10.1
- **Example**: `$config['vpn_health_check_interval'] = 300;`

#### `vpn_connection_timeout`
- **Type**: Integer (seconds)
- **Default**: `10`
- **Description**: Timeout for VPN connection tests
- **Requirements**: 10.1
- **Example**: `$config['vpn_connection_timeout'] = 10;`

#### `vpn_max_retry_attempts`
- **Type**: Integer
- **Default**: `3`
- **Description**: Number of failed connection attempts before marking router as disconnected
- **Requirements**: 10.3
- **Example**: `$config['vpn_max_retry_attempts'] = 3;`

### Alert Configuration

#### `vpn_alert_email`
- **Type**: String (Email Address)
- **Default**: `'admin@example.com'`
- **Description**: Email address to receive VPN alerts
- **Requirements**: 10.3, 10.7
- **Important**: Must be a valid email address
- **Example**: `$config['vpn_alert_email'] = 'admin@example.com';`

#### `vpn_alert_on_disconnect`
- **Type**: Boolean
- **Default**: `true`
- **Description**: Whether to send email alerts when routers disconnect
- **Requirements**: 10.3
- **Example**: `$config['vpn_alert_on_disconnect'] = true;`

#### `vpn_alert_cert_expiry_days`
- **Type**: Array of Integers
- **Default**: `[30, 14, 7]`
- **Description**: Days before certificate expiry to send alert emails
- **Requirements**: 10.5, 10.6, 10.7
- **Example**: `$config['vpn_alert_cert_expiry_days'] = [30, 14, 7];`
- **Note**: Alerts will be sent when certificates expire in 30, 14, and 7 days

## Initial Setup

1. Copy `config.sample.php` to `config.php`
2. Update database settings
3. Update VPN settings:
   - Set `vpn_server_ip` to your VPS public IP
   - Change `vpn_encryption_key` to a random 32-character string
   - Update `vpn_alert_email` to your admin email
4. Adjust other settings as needed for your environment

## Security Recommendations

1. **Encryption Key**: Always change the default `vpn_encryption_key` to a random value
2. **File Permissions**: Ensure `config.php` has restricted permissions (640 or 600)
3. **Storage Directory**: Keep `vpn_storage_dir` outside the web root if possible
4. **Script Permissions**: VPN scripts should be owned by root with 750 permissions

## Troubleshooting

### VPN Configuration Not Working

1. Verify `vpn_enabled` is set to `true`
2. Check that `vpn_server_ip` is correct and accessible
3. Ensure OpenVPN server is running on the specified port
4. Verify `easyrsa_dir` points to correct EasyRSA installation

### Certificate Issues

1. Check `easyrsa_dir` path is correct
2. Verify CA and server certificates exist
3. Ensure `vpn_cert_validity_days` is reasonable (365 recommended)

### Monitoring Not Working

1. Verify cron jobs are configured (see `system/CRON_JOBS_README.md`)
2. Check `vpn_health_check_interval` is not too short (minimum 60 seconds)
3. Ensure `vpn_alert_email` is valid

### Storage Issues

1. Verify `vpn_storage_dir` exists and is writable by web server
2. Check disk space availability
3. Ensure proper directory permissions (750)

## Related Documentation

- `system/VPN_MIGRATION_README.md` - Database schema and migration
- `system/scripts/vpn/README.md` - VPN management scripts
- `system/CRON_JOBS_README.md` - Cron job configuration
- `system/autoload/VPN_CLASSES_README.md` - PHP service classes

## Requirements Mapping

This configuration addresses the following requirements:

- **1.1**: Router type selection (vpn_enabled)
- **3.1**: Certificate management (easyrsa_dir, vpn_cert_validity_days, vpn_cert_warning_days)
- **5.1**: OVPN config generation (vpn_server_ip, vpn_server_port, vpn_protocol, vpn_subnet)
- **6.1**: Service management (vpn_storage_dir, vpn_script_dir)
- **10.1**: Connection monitoring (vpn_health_check_interval, vpn_connection_timeout)
- **10.3**: Disconnect alerts (vpn_alert_on_disconnect, vpn_max_retry_attempts)
- **10.5, 10.6, 10.7**: Certificate expiry alerts (vpn_alert_cert_expiry_days, vpn_alert_email)
