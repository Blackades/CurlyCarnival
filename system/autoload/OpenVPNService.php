<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * OpenVPNService class
 * Manages OpenVPN service and user authentication
 */
class OpenVPNService
{
    private $auth_script_path;
    private $server_config_path;
    private $vpn_config_dir;
    private $script_dir;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = null)
    {
        global $config;
        
        $this->auth_script_path = '/etc/openvpn/check-auth.sh';
        $this->server_config_path = '/etc/openvpn/server.conf';
        $this->vpn_config_dir = $config['vpn_storage_dir'] ?? '/var/www/html/system/storage/vpn-configs';
        $this->script_dir = $config['vpn_script_dir'] ?? '/var/www/html/system/scripts/vpn';
    }

    /**
     * Add VPN user
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @throws VPNException
     */
    public function addVPNUser($username, $password)
    {
        $scriptPath = $this->script_dir . '/add_vpn_user.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Add VPN user script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        // Backup auth script before modification
        $this->backupAuthScript();

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' ' . 
                   escapeshellarg($username) . ' ' . 
                   escapeshellarg($password) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode === 1) {
            throw new VPNException(
                'VPN username already exists',
                VPNException::ERR_USERNAME_EXISTS,
                ['username' => $username]
            );
        } elseif ($exitCode !== 0) {
            throw new VPNException(
                'Failed to add VPN user: ' . implode("\n", $output),
                VPNException::ERR_SCRIPT_EXECUTION,
                ['username' => $username]
            );
        }

        return true;
    }

    /**
     * Remove VPN user
     *
     * @param string $username
     * @return bool
     * @throws VPNException
     */
    public function removeVPNUser($username)
    {
        $scriptPath = $this->script_dir . '/remove_vpn_user.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Remove VPN user script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        // Backup auth script before modification
        $this->backupAuthScript();

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' ' . 
                   escapeshellarg($username) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new VPNException(
                'Failed to remove VPN user: ' . implode("\n", $output),
                VPNException::ERR_SCRIPT_EXECUTION,
                ['username' => $username]
            );
        }

        return true;
    }

    /**
     * Update VPN user password
     *
     * @param string $username
     * @param string $newPassword
     * @return bool
     * @throws VPNException
     */
    public function updateVPNUser($username, $newPassword)
    {
        // Remove and re-add user with new password
        $this->removeVPNUser($username);
        $this->addVPNUser($username, $newPassword);
        
        return true;
    }

    /**
     * Generate OVPN configuration file
     *
     * @param string $clientName
     * @param string $serverIP
     * @param int $serverPort
     * @param string $certDir
     * @return string Path to generated OVPN file
     * @throws VPNException
     */
    public function generateOVPNConfig($clientName, $serverIP, $serverPort, $certDir)
    {
        $scriptPath = $this->script_dir . '/generate_ovpn_config.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Generate OVPN config script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' ' . 
                   escapeshellarg($clientName) . ' ' . 
                   escapeshellarg($serverIP) . ' ' . 
                   escapeshellarg($serverPort) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new VPNException(
                'Failed to generate OVPN config: ' . implode("\n", $output),
                VPNException::ERR_SCRIPT_EXECUTION,
                ['client' => $clientName]
            );
        }

        // Parse output: SUCCESS:file_path
        $outputStr = implode("\n", $output);
        if (preg_match('/SUCCESS:(.+)/', $outputStr, $matches)) {
            return trim($matches[1]);
        }

        throw new VPNException(
            'Failed to parse OVPN config generation output',
            VPNException::ERR_SCRIPT_EXECUTION
        );
    }

    /**
     * Restart OpenVPN service
     *
     * @return bool
     * @throws VPNException
     */
    public function restartService()
    {
        $scriptPath = $this->script_dir . '/restart_openvpn.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Restart OpenVPN script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode === 1) {
            throw new VPNException(
                'OpenVPN configuration test failed',
                VPNException::ERR_SERVICE_RESTART,
                ['output' => implode("\n", $output)]
            );
        } elseif ($exitCode === 2) {
            throw new VPNException(
                'OpenVPN service restart failed',
                VPNException::ERR_SERVICE_RESTART,
                ['output' => implode("\n", $output)]
            );
        } elseif ($exitCode !== 0) {
            throw new VPNException(
                'OpenVPN service restart error: ' . implode("\n", $output),
                VPNException::ERR_SERVICE_RESTART
            );
        }

        return true;
    }

    /**
     * Get OpenVPN service status
     *
     * @return array
     */
    public function getServiceStatus()
    {
        $scriptPath = $this->script_dir . '/get_vpn_status.sh';
        
        if (!file_exists($scriptPath)) {
            return [
                'running' => false,
                'message' => 'Status script not found'
            ];
        }

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        return [
            'running' => ($exitCode === 0),
            'message' => implode("\n", $output)
        ];
    }

    /**
     * Get connected VPN clients
     *
     * @return array
     */
    public function getConnectedClients()
    {
        $scriptPath = $this->script_dir . '/get_connected_clients.sh';
        
        if (!file_exists($scriptPath)) {
            return [];
        }

        $command = 'sudo ' . escapeshellarg($scriptPath) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            return [];
        }

        // Parse output: username,vpn_ip,bytes_received,bytes_sent,connected_since
        $clients = [];
        foreach ($output as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, 'Common Name') !== false) {
                continue;
            }
            
            $parts = explode(',', $line);
            if (count($parts) >= 5) {
                $clients[] = [
                    'username' => $parts[0],
                    'vpn_ip' => $parts[1],
                    'bytes_received' => $parts[2],
                    'bytes_sent' => $parts[3],
                    'connected_since' => $parts[4]
                ];
            }
        }

        return $clients;
    }

    /**
     * Get specific client connection info
     *
     * @param string $vpnIP
     * @return array|null
     */
    public function getClientConnectionInfo($vpnIP)
    {
        $clients = $this->getConnectedClients();
        
        foreach ($clients as $client) {
            if ($client['vpn_ip'] === $vpnIP) {
                return $client;
            }
        }

        return null;
    }

    /**
     * Backup authentication script
     *
     * @return string Backup file path
     * @throws VPNException
     */
    private function backupAuthScript()
    {
        if (!file_exists($this->auth_script_path)) {
            throw new VPNException(
                'Authentication script not found',
                VPNException::ERR_FILE_NOT_FOUND,
                ['path' => $this->auth_script_path]
            );
        }

        $backupDir = $this->vpn_config_dir . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $backupDir . '/check-auth_' . $timestamp . '.sh';
        
        if (!copy($this->auth_script_path, $backupPath)) {
            throw new VPNException(
                'Failed to backup authentication script',
                VPNException::ERR_AUTH_SCRIPT_BACKUP
            );
        }

        return $backupPath;
    }

    /**
     * Restore authentication script from backup
     *
     * @param string $backupPath
     * @return bool
     * @throws VPNException
     */
    private function restoreAuthScript($backupPath)
    {
        if (!file_exists($backupPath)) {
            throw new VPNException(
                'Backup file not found',
                VPNException::ERR_FILE_NOT_FOUND,
                ['path' => $backupPath]
            );
        }

        $command = 'sudo cp ' . escapeshellarg($backupPath) . ' ' . 
                   escapeshellarg($this->auth_script_path) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new VPNException(
                'Failed to restore authentication script',
                VPNException::ERR_AUTH_SCRIPT_RESTORE,
                ['output' => implode("\n", $output)]
            );
        }

        return true;
    }
}
