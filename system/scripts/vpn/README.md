# VPN Management Scripts

This directory contains bash scripts for managing OpenVPN authentication and certificate operations for phpnuxbill's MikroTik remote router integration.

## Scripts Overview

### 1. check_certificates.sh
Validates CA and server certificate expiry dates.

**Usage:**
```bash
sudo ./check_certificates.sh
```

**Exit Codes:**
- `0` - Valid certificates
- `1` - CA certificate missing
- `2` - CA certificate expiring soon (< 30 days)
- `3` - Server certificate missing
- `4` - Server certificate expiring soon (< 30 days)

**Output Format:**
- `VALID:days` - Certificates are valid
- `CA_EXPIRING:days` - CA certificate expiring soon
- `SERVER_CERT_EXPIRING:days` - Server certificate expiring soon
- `ERROR:message` - Error occurred

---

### 2. add_vpn_user.sh
Adds a VPN user to the OpenVPN authentication script with automatic backup.

**Usage:**
```bash
sudo ./add_vpn_user.sh <username> <password>
```

**Exit Codes:**
- `0` - Success
- `1` - User already exists
- `2` - Script error (backup failed, write failed, etc.)

**Output:**
- `SUCCESS:User <username> added successfully`
- `ERROR:message`

**Features:**
- Creates timestamped backup before modification
- Validates username uniqueness
- Logs all operations to `/var/log/phpnuxbill/vpn-user-management.log`

---

### 3. remove_vpn_user.sh
Removes a VPN user from the OpenVPN authentication script.

**Usage:**
```bash
sudo ./remove_vpn_user.sh <username>
```

**Exit Codes:**
- `0` - Success
- `1` - User not found
- `2` - Script error

**Output:**
- `SUCCESS:User <username> removed successfully`
- `ERROR:message`

---

### 4. generate_client_cert.sh
Generates a client certificate using EasyRSA.

**Usage:**
```bash
sudo ./generate_client_cert.sh <client_name> [validity_days]
```

**Parameters:**
- `client_name` - Unique identifier for the client (alphanumeric, underscore, hyphen only)
- `validity_days` - Certificate validity period (default: 365 days)

**Exit Codes:**
- `0` - Success
- `1` - Certificate generation failed
- `2` - Invalid arguments

**Output:**
- `SUCCESS:path/to/certificate.crt`
- `ERROR:message`

---

### 5. revoke_client_cert.sh
Revokes a client certificate and updates the CRL.

**Usage:**
```bash
sudo ./revoke_client_cert.sh <client_name>
```

**Exit Codes:**
- `0` - Success
- `1` - Revocation failed
- `2` - Invalid arguments

**Output:**
- `SUCCESS:Certificate for <client_name> revoked successfully`
- `ERROR:message`

**Features:**
- Automatically generates updated CRL
- Copies CRL to OpenVPN directory
- Logs all operations

---

### 6. generate_ovpn_config.sh
Generates an OVPN configuration file with embedded certificates.

**Usage:**
```bash
sudo ./generate_ovpn_config.sh <client_name> <server_ip> <server_port> [protocol] [output_dir]
```

**Parameters:**
- `client_name` - Client certificate name
- `server_ip` - VPN server public IP address
- `server_port` - VPN server port (typically 1194)
- `protocol` - tcp or udp (default: tcp)
- `output_dir` - Output directory (default: /var/www/html/system/storage/vpn-configs)

**Exit Codes:**
- `0` - Success
- `1` - File creation failed
- `2` - Invalid arguments

**Output:**
- `SUCCESS:path/to/config.ovpn`
- `ERROR:message`

**Features:**
- Embeds CA certificate, client certificate, and private key
- Configures AES-256-GCM cipher and SHA256 authentication
- Sets proper file permissions (600)

---

### 7. restart_openvpn.sh
Restarts the OpenVPN service with configuration testing.

**Usage:**
```bash
sudo ./restart_openvpn.sh
```

**Exit Codes:**
- `0` - Success
- `1` - Configuration test failed
- `2` - Restart failed

**Output:**
- `SUCCESS:OpenVPN service restarted successfully`
- `ERROR:message with status details`

**Features:**
- Tests configuration before restart
- Verifies service is running after restart
- Logs all operations to `/var/log/phpnuxbill/vpn-service.log`

---

### 8. get_vpn_status.sh
Checks OpenVPN service status and returns JSON output.

**Usage:**
```bash
sudo ./get_vpn_status.sh
```

**Exit Codes:**
- `0` - Service running
- `1` - Service stopped
- `2` - Service failed/error

**Output (JSON):**
```json
{
  "status": "running|stopped|failed",
  "service": "openvpn@server",
  "pid": "12345",
  "uptime": "3600s",
  "timestamp": "2024-12-03 10:30:00"
}
```

---

### 9. get_connected_clients.sh
Lists all connected VPN clients with connection details.

**Usage:**
```bash
sudo ./get_connected_clients.sh
```

**Exit Codes:**
- `0` - Success (clients found)
- `1` - Status file not found or no clients
- `2` - Error reading status

**Output (JSON):**
```json
{
  "timestamp": "2024-12-03 10:30:00",
  "status_file": "/var/log/openvpn/openvpn-status.log",
  "clients": [
    {
      "common_name": "mikrotik-branch1",
      "real_address": "203.0.113.50:54321",
      "virtual_address": "10.8.0.2",
      "bytes_received": "1048576",
      "bytes_sent": "2097152",
      "connected_since": "2024-12-03 08:00:00"
    }
  ]
}
```

---

### 10. cleanup_expired_certs.sh
Archives and removes expired certificate files.

**Usage:**
```bash
sudo ./cleanup_expired_certs.sh
```

**Exit Codes:**
- `0` - Success
- `1` - Error during cleanup

**Output (JSON):**
```json
{
  "timestamp": "2024-12-03 10:30:00",
  "expired_found": 3,
  "archived": 3,
  "errors": 0,
  "archive_location": "/etc/openvpn/expired-certs"
}
```

**Features:**
- Archives expired certificates before deletion
- Organizes archives by date
- Removes certificate, key, and OVPN config files
- Logs all operations to `/var/log/phpnuxbill/vpn-cleanup.log`

---

## Installation

### 1. Set Permissions
Run the permission setup script as root:

```bash
sudo ./set_permissions.sh
```

This will set all scripts to:
- Owner: `root:www-data`
- Permissions: `750` (rwxr-x---)

### 2. Configure Sudo Access
Add the following to `/etc/sudoers.d/phpnuxbill-vpn`:

```bash
# PHPNuxBill VPN Management Scripts
Defaults:www-data !requiretty

# Certificate management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/check_certificates.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/generate_client_cert.sh *
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/revoke_client_cert.sh *

# VPN user management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/add_vpn_user.sh * *
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/remove_vpn_user.sh *

# OpenVPN service management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/restart_openvpn.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/get_vpn_status.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/get_connected_clients.sh

# Configuration generation
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/generate_ovpn_config.sh * * *

# Cleanup
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/cleanup_expired_certs.sh
```

### 3. Create Required Directories

```bash
sudo mkdir -p /var/log/phpnuxbill
sudo mkdir -p /etc/openvpn/backups
sudo mkdir -p /etc/openvpn/expired-certs
sudo mkdir -p /var/www/html/system/storage/vpn-configs

sudo chown www-data:www-data /var/log/phpnuxbill
sudo chown www-data:www-data /var/www/html/system/storage/vpn-configs
sudo chmod 750 /var/www/html/system/storage/vpn-configs
```

## Environment Variables

All scripts support the following environment variables for customization:

- `EASYRSA_DIR` - EasyRSA installation directory (default: `/etc/openvpn/easy-rsa`)
- `AUTH_SCRIPT` - OpenVPN authentication script path (default: `/etc/openvpn/check-auth.sh`)
- `BACKUP_DIR` - Backup directory for auth script (default: `/etc/openvpn/backups`)
- `LOG_FILE` - Log file path (varies by script)
- `OPENVPN_SERVICE` - Systemd service name (default: `openvpn@server`)
- `OPENVPN_CONFIG` - OpenVPN config file (default: `/etc/openvpn/server.conf`)
- `OPENVPN_STATUS_FILE` - OpenVPN status log (default: `/var/log/openvpn/openvpn-status.log`)

## Testing

Test each script individually:

```bash
# Test certificate check
sudo ./check_certificates.sh

# Test VPN user management
sudo ./add_vpn_user.sh test-user TestPass123
sudo ./remove_vpn_user.sh test-user

# Test certificate generation
sudo ./generate_client_cert.sh test-client 365
sudo ./revoke_client_cert.sh test-client

# Test OVPN config generation
sudo ./generate_ovpn_config.sh test-client 203.0.113.10 1194

# Test service management
sudo ./get_vpn_status.sh
sudo ./get_connected_clients.sh

# Test cleanup
sudo ./cleanup_expired_certs.sh
```

## Logging

All scripts log operations to `/var/log/phpnuxbill/`:
- `vpn-user-management.log` - User add/remove operations
- `vpn-cert-management.log` - Certificate operations
- `vpn-service.log` - Service restart operations
- `vpn-cleanup.log` - Cleanup operations

## Security Considerations

1. **Script Permissions**: All scripts are owned by root and executable only by root and www-data group
2. **Sudo Access**: Web server has limited sudo access only to specific scripts
3. **Input Validation**: All scripts validate input parameters
4. **Backup Strategy**: Authentication script is backed up before modifications
5. **Logging**: All operations are logged with timestamps
6. **File Permissions**: Generated certificates and configs have restrictive permissions

## Troubleshooting

### Script Permission Denied
```bash
sudo chmod 750 /var/www/html/system/scripts/vpn/*.sh
sudo chown root:www-data /var/www/html/system/scripts/vpn/*.sh
```

### Sudo Access Issues
Verify sudoers configuration:
```bash
sudo visudo -c
sudo cat /etc/sudoers.d/phpnuxbill-vpn
```

### EasyRSA Not Found
Install and initialize EasyRSA:
```bash
sudo apt-get install easy-rsa
sudo make-cadir /etc/openvpn/easy-rsa
cd /etc/openvpn/easy-rsa
sudo ./easyrsa init-pki
sudo ./easyrsa build-ca
sudo ./easyrsa build-server-full server nopass
```

### Log Files Not Created
Ensure log directory exists and is writable:
```bash
sudo mkdir -p /var/log/phpnuxbill
sudo chown www-data:www-data /var/log/phpnuxbill
```

## Integration with phpnuxbill

These scripts are called by PHP service classes:
- `VPNConfigurationService` - Orchestrates router configuration
- `CertificateManager` - Manages certificates
- `OpenVPNService` - Manages VPN users and service
- `VPNMonitoringService` - Monitors connections

See the design document for detailed integration information.
