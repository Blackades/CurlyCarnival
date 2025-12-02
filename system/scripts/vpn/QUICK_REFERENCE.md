# VPN Scripts Quick Reference

## Quick Command Reference

### Certificate Management
```bash
# Check certificate validity
sudo ./check_certificates.sh

# Generate client certificate (365 days validity)
sudo ./generate_client_cert.sh mikrotik-branch1 365

# Revoke client certificate
sudo ./revoke_client_cert.sh mikrotik-branch1

# Cleanup expired certificates
sudo ./cleanup_expired_certs.sh
```

### VPN User Management
```bash
# Add VPN user
sudo ./add_vpn_user.sh mikrotik-branch1 SecurePass123

# Remove VPN user
sudo ./remove_vpn_user.sh mikrotik-branch1
```

### Configuration Generation
```bash
# Generate OVPN config file
sudo ./generate_ovpn_config.sh mikrotik-branch1 203.0.113.10 1194 tcp

# With custom output directory
sudo ./generate_ovpn_config.sh mikrotik-branch1 203.0.113.10 1194 tcp /custom/path
```

### Service Management
```bash
# Get OpenVPN service status (JSON output)
sudo ./get_vpn_status.sh

# List connected clients (JSON output)
sudo ./get_connected_clients.sh

# Restart OpenVPN service (with config test)
sudo ./restart_openvpn.sh
```

### Setup & Testing
```bash
# Set proper permissions on all scripts
sudo ./set_permissions.sh

# Run test suite
sudo ./test_scripts.sh
```

## Exit Codes Summary

| Script | 0 | 1 | 2 | 3 | 4 |
|--------|---|---|---|---|---|
| check_certificates.sh | Valid | CA missing | CA expiring | Server cert missing | Server cert expiring |
| add_vpn_user.sh | Success | User exists | Script error | - | - |
| remove_vpn_user.sh | Success | User not found | Script error | - | - |
| generate_client_cert.sh | Success | Generation failed | Invalid args | - | - |
| revoke_client_cert.sh | Success | Revocation failed | Invalid args | - | - |
| generate_ovpn_config.sh | Success | File creation failed | Invalid args | - | - |
| restart_openvpn.sh | Success | Config test failed | Restart failed | - | - |
| get_vpn_status.sh | Running | Stopped | Failed/Error | - | - |
| get_connected_clients.sh | Success | No clients/file not found | Read error | - | - |
| cleanup_expired_certs.sh | Success | Cleanup error | - | - | - |

## Output Formats

### check_certificates.sh
```
VALID:365
CA_EXPIRING:25
SERVER_CERT_EXPIRING:15
ERROR:CA certificate not found
```

### add_vpn_user.sh / remove_vpn_user.sh
```
SUCCESS:User mikrotik-branch1 added successfully
ERROR:User mikrotik-branch1 already exists
```

### generate_client_cert.sh
```
SUCCESS:/etc/openvpn/easy-rsa/pki/issued/mikrotik-branch1.crt
ERROR:Certificate for mikrotik-branch1 already exists
```

### generate_ovpn_config.sh
```
SUCCESS:/var/www/html/system/storage/vpn-configs/mikrotik-branch1.ovpn
ERROR:Client certificate not found
```

### get_vpn_status.sh (JSON)
```json
{
  "status": "running",
  "service": "openvpn@server",
  "pid": "12345",
  "uptime": "3600s",
  "timestamp": "2024-12-03 10:30:00"
}
```

### get_connected_clients.sh (JSON)
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

### cleanup_expired_certs.sh (JSON)
```json
{
  "timestamp": "2024-12-03 10:30:00",
  "expired_found": 3,
  "archived": 3,
  "errors": 0,
  "archive_location": "/etc/openvpn/expired-certs"
}
```

## Common Workflows

### Complete Router Setup
```bash
# 1. Check certificates are valid
sudo ./check_certificates.sh

# 2. Add VPN user
sudo ./add_vpn_user.sh mikrotik-branch1 SecurePass123

# 3. Generate client certificate
sudo ./generate_client_cert.sh mikrotik-branch1 365

# 4. Generate OVPN config
sudo ./generate_ovpn_config.sh mikrotik-branch1 203.0.113.10 1194

# 5. Restart OpenVPN service
sudo ./restart_openvpn.sh

# 6. Verify service is running
sudo ./get_vpn_status.sh
```

### Router Removal
```bash
# 1. Remove VPN user
sudo ./remove_vpn_user.sh mikrotik-branch1

# 2. Revoke certificate
sudo ./revoke_client_cert.sh mikrotik-branch1

# 3. Restart OpenVPN service
sudo ./restart_openvpn.sh
```

### Monitoring
```bash
# Check service status
sudo ./get_vpn_status.sh | jq .

# List connected clients
sudo ./get_connected_clients.sh | jq .

# Check certificate expiry
sudo ./check_certificates.sh
```

### Maintenance
```bash
# Cleanup expired certificates (run weekly)
sudo ./cleanup_expired_certs.sh

# Check for expiring certificates
sudo ./check_certificates.sh
```

## Log Files

| Script | Log File |
|--------|----------|
| add_vpn_user.sh | /var/log/phpnuxbill/vpn-user-management.log |
| remove_vpn_user.sh | /var/log/phpnuxbill/vpn-user-management.log |
| revoke_client_cert.sh | /var/log/phpnuxbill/vpn-cert-management.log |
| restart_openvpn.sh | /var/log/phpnuxbill/vpn-service.log |
| cleanup_expired_certs.sh | /var/log/phpnuxbill/vpn-cleanup.log |

## Environment Variables

Override defaults by setting environment variables:

```bash
# Use custom EasyRSA directory
EASYRSA_DIR=/custom/path/easy-rsa sudo ./generate_client_cert.sh client1

# Use custom authentication script
AUTH_SCRIPT=/custom/check-auth.sh sudo ./add_vpn_user.sh user1 pass1

# Use custom OpenVPN service name
OPENVPN_SERVICE=openvpn@custom sudo ./restart_openvpn.sh
```

## Troubleshooting

### Permission Denied
```bash
sudo ./set_permissions.sh
```

### EasyRSA Not Found
```bash
# Install EasyRSA
sudo apt-get install easy-rsa

# Initialize PKI
sudo make-cadir /etc/openvpn/easy-rsa
cd /etc/openvpn/easy-rsa
sudo ./easyrsa init-pki
sudo ./easyrsa build-ca
sudo ./easyrsa build-server-full server nopass
```

### OpenVPN Service Not Found
```bash
# Check service name
systemctl list-units | grep openvpn

# Use correct service name
OPENVPN_SERVICE=openvpn sudo ./get_vpn_status.sh
```

### Log Directory Not Writable
```bash
sudo mkdir -p /var/log/phpnuxbill
sudo chown www-data:www-data /var/log/phpnuxbill
```

## PHP Integration Example

```php
// Check certificates
$output = shell_exec('sudo /var/www/html/system/scripts/vpn/check_certificates.sh 2>&1');
$exitCode = $this->getLastExitCode();

// Add VPN user
$username = escapeshellarg($vpnUsername);
$password = escapeshellarg($vpnPassword);
$output = shell_exec("sudo /var/www/html/system/scripts/vpn/add_vpn_user.sh $username $password 2>&1");

// Generate certificate
$clientName = escapeshellarg($clientName);
$validityDays = escapeshellarg($validityDays);
$output = shell_exec("sudo /var/www/html/system/scripts/vpn/generate_client_cert.sh $clientName $validityDays 2>&1");

// Get connected clients (JSON)
$json = shell_exec('sudo /var/www/html/system/scripts/vpn/get_connected_clients.sh 2>&1');
$data = json_decode($json, true);
```
