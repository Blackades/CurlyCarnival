<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

use PEAR2\Net\RouterOS;

/**
 * VPNConfigurationService class
 * Orchestrates complete VPN configuration workflow
 */
class VPNConfigurationService
{
    private $certificateManager;
    private $openVPNService;
    private $scriptGenerator;
    private $rollbackManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->certificateManager = new CertificateManager();
        $this->openVPNService = new OpenVPNService();
        $this->scriptGenerator = new MikroTikScriptGenerator();
        $this->rollbackManager = new VPNRollbackManager();
    }

    /**
     * Create complete remote router configuration
     *
     * @param array $routerData
     * @return array Result with router_id and config_package_path
     * @throws VPNException
     */
    public function createRemoteRouterConfiguration($routerData)
    {
        global $config, $admin;
        
        try {
            // Step 1: Validate VPN credentials
            $this->validateVPNCredentials(
                $routerData['vpn_username'],
                $routerData['vpn_password']
            );

            // Step 2: Check username availability
            if (!$this->checkUsernameAvailability($routerData['vpn_username'])) {
                throw new VPNException(
                    'VPN username already exists',
                    VPNException::ERR_USERNAME_EXISTS
                );
            }

            // Step 3: Check certificate validity
            $certCheck = $this->certificateManager->checkCertificateValidity();
            if ($certCheck['status'] === 'ca_expiring' || $certCheck['status'] === 'server_cert_expiring') {
                // Log warning but continue
            }

            // Step 4: Add VPN user
            $this->openVPNService->addVPNUser(
                $routerData['vpn_username'],
                $routerData['vpn_password']
            );
            $this->rollbackManager->addRollbackAction('removeVPNUser', [
                'username' => $routerData['vpn_username']
            ]);

            // Step 5: Generate client certificate
            $clientName = 'router-' . preg_replace('/[^a-zA-Z0-9-]/', '', $routerData['name']);
            $validityDays = $routerData['cert_validity_days'] ?? 365;
            
            $certPath = $this->certificateManager->generateClientCertificate(
                $clientName,
                $validityDays
            );
            $this->rollbackManager->addRollbackAction('revokeCertificate', [
                'clientName' => $clientName
            ]);

            // Step 6: Generate OVPN configuration
            $serverIP = $config['vpn_server_ip'];
            $serverPort = $config['vpn_server_port'] ?? 1194;
            
            $ovpnPath = $this->openVPNService->generateOVPNConfig(
                $clientName,
                $serverIP,
                $serverPort,
                $certPath
            );

            // Step 7: Calculate certificate expiry
            $certExpiryDate = date('Y-m-d', strtotime("+{$validityDays} days"));

            // Step 8: Get next available VPN IP
            $vpnIP = $this->getNextAvailableVPNIP();

            // Step 9: Create router database entry
            $router = ORM::for_table('tbl_routers')->create();
            $router->name = $routerData['name'];
            $router->ip_address = $vpnIP; // Will be updated after MikroTik connects
            $router->username = $routerData['api_username'];
            $router->password = $routerData['api_password'];
            $router->description = $routerData['description'] ?? '';
            $router->enabled = 1;
            $router->connection_type = 'remote';
            $router->vpn_username = $routerData['vpn_username'];
            $router->vpn_password_hash = VPNPasswordManager::hashPassword($routerData['vpn_password']);
            $router->vpn_ip = $vpnIP;
            $router->certificate_path = $certPath;
            $router->certificate_expiry = $certExpiryDate;
            $router->ovpn_status = 'pending';
            $router->port = $routerData['api_port'] ?? 8728;
            $router->save();
            
            $routerId = $router->id();
            $this->rollbackManager->addRollbackAction('deleteRouter', [
                'routerId' => $routerId
            ]);

            // Step 10: Create certificate database entry
            $certRecord = ORM::for_table('tbl_vpn_certificates')->create();
            $certRecord->router_id = $routerId;
            $certRecord->client_name = $clientName;
            $certRecord->certificate_path = $certPath . '/' . $clientName . '.crt';
            $certRecord->key_path = $certPath . '/' . $clientName . '.key';
            $certRecord->ca_path = $certPath . '/ca.crt';
            $certRecord->ovpn_file_path = $ovpnPath;
            $certRecord->issued_date = date('Y-m-d H:i:s');
            $certRecord->expiry_date = $certExpiryDate;
            $certRecord->status = 'active';
            $certRecord->save();
            
            $certId = $certRecord->id();
            $this->rollbackManager->addRollbackAction('deleteCertificateRecord', [
                'certificateId' => $certId
            ]);

            // Step 11: Generate MikroTik script
            $scriptConfig = [
                'server_ip' => $serverIP,
                'server_port' => $serverPort,
                'vpn_username' => $routerData['vpn_username'],
                'vpn_password' => $routerData['vpn_password'],
                'ca_name' => 'ca',
                'client_cert_name' => $clientName,
                'api_username' => $routerData['api_username'],
                'api_password' => $routerData['api_password'],
                'api_port' => $routerData['api_port'] ?? 8728,
                'router_name' => $routerData['name'],
                'generated_date' => date('Y-m-d H:i:s')
            ];
            
            $mikrotikScript = $this->scriptGenerator->generateRouterOSScript($scriptConfig);
            $setupInstructions = $this->scriptGenerator->generateSetupInstructions($scriptConfig);

            // Step 12: Save scripts to files
            $storageDir = $config['vpn_storage_dir'] ?? '/var/www/html/system/storage/vpn-configs';
            $routerDir = $storageDir . '/router-' . $routerId;
            
            if (!is_dir($routerDir)) {
                mkdir($routerDir, 0750, true);
            }

            $scriptPath = $routerDir . '/mikrotik-setup.rsc';
            $instructionsPath = $routerDir . '/SETUP_INSTRUCTIONS.txt';
            
            file_put_contents($scriptPath, $mikrotikScript);
            file_put_contents($instructionsPath, $setupInstructions);

            // Step 13: Create configuration package
            $packageFiles = [
                ['path' => $certPath . '/ca.crt', 'name' => 'ca.crt'],
                ['path' => $certPath . '/' . $clientName . '.crt', 'name' => $clientName . '.crt'],
                ['path' => $certPath . '/' . $clientName . '.key', 'name' => $clientName . '.key'],
                ['path' => $ovpnPath, 'name' => $clientName . '.ovpn'],
                ['path' => $scriptPath, 'name' => 'mikrotik-setup.rsc'],
                ['path' => $instructionsPath, 'name' => 'SETUP_INSTRUCTIONS.txt']
            ];
            
            $packagePath = $this->scriptGenerator->createConfigurationPackage($routerId, $packageFiles);
            
            // Update router with package path
            $router->config_package_path = $packagePath;
            $router->save();

            // Step 14: Restart OpenVPN service
            $this->openVPNService->restartService();

            // Step 15: Log audit action
            $this->logAuditAction(
                $routerId,
                'vpn_router_created',
                $admin['id'] ?? 0,
                [
                    'vpn_username' => $routerData['vpn_username'],
                    'router_name' => $routerData['name'],
                    'vpn_ip' => $vpnIP
                ],
                'success'
            );

            // Clear rollback stack on success
            $this->rollbackManager->clearRollback();

            return [
                'success' => true,
                'router_id' => $routerId,
                'config_package_path' => $packagePath,
                'vpn_ip' => $vpnIP,
                'certificate_expiry' => $certExpiryDate
            ];

        } catch (Exception $e) {
            // Execute rollback on any error
            $this->rollbackManager->executeRollback();
            
            // Log failed action
            if (isset($routerId)) {
                $this->logAuditAction(
                    $routerId,
                    'vpn_router_creation_failed',
                    $admin['id'] ?? 0,
                    [
                        'error' => $e->getMessage(),
                        'code' => $e->getCode()
                    ],
                    'failed'
                );
            }

            throw $e;
        }
    }

    /**
     * Validate VPN credentials
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @throws VPNException
     */
    public function validateVPNCredentials($username, $password)
    {
        VPNInputValidator::validateUsername($username);
        VPNInputValidator::validatePassword($password);
        return true;
    }

    /**
     * Check if username is available
     *
     * @param string $username
     * @return bool
     */
    public function checkUsernameAvailability($username)
    {
        // Check in database
        $existing = ORM::for_table('tbl_routers')
            ->where('vpn_username', $username)
            ->find_one();
        
        if ($existing) {
            return false;
        }

        // Check in auth script (if accessible)
        $authScriptPath = '/etc/openvpn/check-auth.sh';
        if (file_exists($authScriptPath)) {
            $content = file_get_contents($authScriptPath);
            if (strpos($content, '"' . $username . '"') !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Rollback configuration
     *
     * @param int $routerId
     * @return array
     */
    public function rollbackConfiguration($routerId)
    {
        return $this->rollbackManager->executeRollback();
    }

    /**
     * Test VPN connection
     *
     * @param string $vpnIP
     * @param string $apiUser
     * @param string $apiPass
     * @param int $apiPort
     * @return array Test results
     */
    public function testVPNConnection($vpnIP, $apiUser, $apiPass, $apiPort = 8728)
    {
        $results = [
            'vpn_reachable' => false,
            'api_accessible' => false,
            'authentication' => false,
            'router_identity' => null,
            'errors' => []
        ];

        // Test 1: Ping VPN IP
        $pingCommand = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
            ? "ping -n 1 -w 5000 " . escapeshellarg($vpnIP)
            : "ping -c 1 -W 5 " . escapeshellarg($vpnIP);
        
        exec($pingCommand . " 2>&1", $pingOutput, $pingExit);
        
        if ($pingExit === 0) {
            $results['vpn_reachable'] = true;
        } else {
            $results['errors'][] = 'VPN IP is not reachable';
            return $results;
        }

        // Test 2: Check API port
        $connection = @fsockopen($vpnIP, $apiPort, $errno, $errstr, 10);
        
        if (is_resource($connection)) {
            fclose($connection);
            $results['api_accessible'] = true;
        } else {
            $results['errors'][] = "API port not accessible: {$errstr}";
            return $results;
        }

        // Test 3: Try API authentication
        try {
            global $_app_stage;
            if ($_app_stage == 'demo') {
                $results['authentication'] = true;
                $results['router_identity'] = 'Demo Router';
                return $results;
            }

            $client = new RouterOS\Client($vpnIP, $apiUser, $apiPass, $apiPort);
            
            // Get router identity
            $request = new RouterOS\Request('/system/identity/print');
            $response = $client->sendSync($request);
            
            $results['authentication'] = true;
            $results['router_identity'] = $response->getProperty('name');
            
        } catch (Exception $e) {
            $results['errors'][] = 'API authentication failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get next available VPN IP
     *
     * @return string
     */
    public function getNextAvailableVPNIP()
    {
        global $config;
        
        $subnet = $config['vpn_subnet'] ?? '10.8.0.0/24';
        
        // Parse subnet
        list($network, $cidr) = explode('/', $subnet);
        $parts = explode('.', $network);
        $baseIP = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.';
        
        // Get all used IPs
        $usedIPs = [];
        $routers = ORM::for_table('tbl_routers')
            ->where('connection_type', 'remote')
            ->where_not_null('vpn_ip')
            ->find_many();
        
        foreach ($routers as $router) {
            $usedIPs[] = $router['vpn_ip'];
        }

        // Find next available IP (start from .2, .1 is usually gateway)
        for ($i = 2; $i < 254; $i++) {
            $testIP = $baseIP . $i;
            if (!in_array($testIP, $usedIPs)) {
                return $testIP;
            }
        }

        throw new VPNException(
            'No available VPN IP addresses in subnet',
            VPNException::ERR_CONNECTION_FAILED
        );
    }

    /**
     * Log audit action
     *
     * @param int $routerId
     * @param string $action
     * @param int $adminId
     * @param array $details
     * @param string $status
     * @return void
     */
    private function logAuditAction($routerId, $action, $adminId, $details, $status = 'success')
    {
        $log = ORM::for_table('tbl_vpn_audit_log')->create();
        $log->router_id = $routerId;
        $log->action = $action;
        $log->admin_id = $adminId;
        $log->details = json_encode($details);
        $log->ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $log->status = $status;
        $log->error_message = ($status === 'failed' && isset($details['error'])) 
            ? $details['error'] 
            : null;
        $log->created_at = date('Y-m-d H:i:s');
        $log->save();
    }
}
