<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * MikroTikScriptGenerator class
 * Generates MikroTik RouterOS configuration scripts
 */
class MikroTikScriptGenerator
{
    /**
     * Generate RouterOS configuration script
     *
     * @param array $config
     * @return string Script content
     */
    public function generateRouterOSScript($config)
    {
        $serverIP = $this->sanitizeScriptValues($config['server_ip']);
        $serverPort = $this->sanitizeScriptValues($config['server_port']);
        $vpnUsername = $this->sanitizeScriptValues($config['vpn_username']);
        $vpnPassword = $this->sanitizeScriptValues($config['vpn_password']);
        $caName = $this->sanitizeScriptValues($config['ca_name'] ?? 'ca');
        $clientCertName = $this->sanitizeScriptValues($config['client_cert_name']);
        $apiUsername = $this->sanitizeScriptValues($config['api_username']);
        $apiPassword = $this->sanitizeScriptValues($config['api_password']);
        $apiPort = $this->sanitizeScriptValues($config['api_port'] ?? '8728');
        
        $script = <<<SCRIPT
# MikroTik RouterOS OpenVPN Client Configuration Script
# Generated: {$config['generated_date']}
# Router: {$config['router_name']}

# Step 1: Import CA Certificate
/certificate import file-name={$caName}.crt passphrase=""

# Step 2: Import Client Certificate
/certificate import file-name={$clientCertName}.crt passphrase=""

# Step 3: Import Client Private Key
/certificate import file-name={$clientCertName}.key passphrase=""

# Step 4: Create OpenVPN Client Interface
/interface ovpn-client
add name=ovpn-phpnuxbill \\
    connect-to={$serverIP} \\
    port={$serverPort} \\
    mode=ip \\
    protocol=tcp \\
    user={$vpnUsername} \\
    password={$vpnPassword} \\
    certificate={$clientCertName}.crt_0 \\
    auth=sha256 \\
    cipher=aes256-gcm \\
    add-default-route=no \\
    disabled=no

# Step 5: Wait for interface to connect (check status manually)
# Use: /interface ovpn-client print detail

# Step 6: Configure Firewall Rules
# Allow incoming connections from VPN server IP only
/ip firewall filter
add chain=input protocol=tcp dst-port={$apiPort} src-address={$serverIP} action=accept comment="Allow API from phpnuxbill VPN"
add chain=input protocol=tcp dst-port={$apiPort} action=drop comment="Block API from other sources"

# Step 7: Enable API Service
/ip service
set api address={$serverIP}/32 disabled=no port={$apiPort}
set api-ssl disabled=yes

# Step 8: Create API User (if not exists)
/user
add name={$apiUsername} password={$apiPassword} group=full comment="phpnuxbill API access"

# Configuration Complete!
# Please verify:
# 1. OpenVPN connection status: /interface ovpn-client print detail
# 2. Check assigned VPN IP: /ip address print where interface=ovpn-phpnuxbill
# 3. Test API access from phpnuxbill server

SCRIPT;

        return $script;
    }

    /**
     * Generate setup instructions
     *
     * @param array $config
     * @return string Instructions in markdown format
     */
    public function generateSetupInstructions($config)
    {
        $routerName = htmlspecialchars($config['router_name']);
        $serverIP = htmlspecialchars($config['server_ip']);
        $vpnUsername = htmlspecialchars($config['vpn_username']);
        
        $instructions = <<<INSTRUCTIONS
# MikroTik Router Setup Instructions

## Router: {$routerName}

### Prerequisites
- MikroTik router with RouterOS 6.45+ or RouterOS 7.x
- Access to router via Winbox or SSH
- Downloaded configuration package

### Step 1: Upload Certificates
1. Open Winbox and connect to your MikroTik router
2. Go to **Files** menu
3. Drag and drop the following files from the configuration package:
   - `ca.crt`
   - `{$config['client_cert_name']}.crt`
   - `{$config['client_cert_name']}.key`
4. Wait for upload to complete

### Step 2: Run Configuration Script
1. Open **New Terminal** in Winbox
2. Copy the entire content of `mikrotik-setup.rsc` file
3. Paste into the terminal and press Enter
4. Wait for all commands to execute

### Step 3: Verify OpenVPN Connection
1. In terminal, run: `/interface ovpn-client print detail`
2. Check that status shows "connected"
3. Run: `/ip address print where interface=ovpn-phpnuxbill`
4. Note the assigned VPN IP address (should be in 10.8.0.0/24 range)

### Step 4: Test API Access
1. Return to phpnuxbill web interface
2. Click "Test Connection" button on the router configuration page
3. Verify all tests pass:
   - VPN connectivity
   - API port accessibility
   - API authentication

### Step 5: Add Router to phpnuxbill
1. Enter the VPN IP address shown in Step 3
2. Save the router configuration
3. Router is now ready for remote management!

### Troubleshooting

**OpenVPN not connecting:**
- Check that certificates were uploaded correctly
- Verify server IP {$serverIP} is reachable from router
- Check router firewall rules aren't blocking outbound connections

**API test fails:**
- Verify VPN connection is established first
- Check API credentials match what you configured
- Ensure firewall rules were applied correctly

**Need to reconfigure:**
- You can re-run the setup script safely
- It will update existing configuration

### Security Notes
- API access is restricted to VPN server IP only
- Keep your API credentials secure
- Regularly update RouterOS to latest stable version

INSTRUCTIONS;

        return $instructions;
    }

    /**
     * Create configuration package ZIP file
     *
     * @param int $routerId
     * @param array $files Array of file paths to include
     * @return string Path to created ZIP file
     * @throws VPNException
     */
    public function createConfigurationPackage($routerId, $files)
    {
        global $config;
        
        $storageDir = $config['vpn_storage_dir'] ?? '/var/www/html/system/storage/vpn-configs';
        $packageDir = $storageDir . '/packages';
        
        if (!is_dir($packageDir)) {
            if (!mkdir($packageDir, 0750, true)) {
                throw new VPNException(
                    'Failed to create package directory',
                    VPNException::ERR_DIR_CREATE
                );
            }
        }

        $zipPath = $packageDir . '/router-' . $routerId . '-config.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new VPNException(
                'Failed to create ZIP file',
                VPNException::ERR_FILE_WRITE,
                ['path' => $zipPath]
            );
        }

        foreach ($files as $file) {
            if (!file_exists($file['path'])) {
                $zip->close();
                throw new VPNException(
                    'File not found: ' . $file['path'],
                    VPNException::ERR_FILE_NOT_FOUND
                );
            }
            
            $zip->addFile($file['path'], $file['name']);
        }

        $zip->close();
        
        return $zipPath;
    }

    /**
     * Sanitize script values to prevent injection
     *
     * @param string $value
     * @return string
     */
    private function sanitizeScriptValues($value)
    {
        // Remove potentially dangerous characters for RouterOS scripts
        $value = str_replace(['\\', '"', '$', '`', ';', '|', '&'], '', $value);
        return $value;
    }
}
