<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

_admin();
$ui->assign('_title', Lang::T('Network'));
$ui->assign('_system_menu', 'network');

$action = $routes['1'];
$ui->assign('_admin', $admin);

require_once $DEVICE_PATH . DIRECTORY_SEPARATOR . "MikrotikHotspot.php";

if (!in_array($admin['user_type'], ['SuperAdmin', 'Admin'])) {
    _alert(Lang::T('You do not have permission to access this page'), 'danger', "dashboard");
}

$leafletpickerHeader = <<<EOT
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css">
EOT;

switch ($action) {
    case 'add':
        run_hook('view_add_routers'); #HOOK
        $ui->assign('xheader', $leafletpickerHeader);
        $ui->display('admin/routers/add.tpl');
        break;

    case 'add-remote-post':
        // Validate connection type is 'remote'
        $connection_type = _post('connection_type');
        if ($connection_type !== 'remote') {
            r2(getUrl('routers/add'), 'e', Lang::T('Invalid connection type'));
        }

        // Extract and validate all remote router form fields
        $name = _post('name');
        $description = _post('description');
        $vpn_username = _post('vpn_username');
        $vpn_password = _post('vpn_password');
        $vpn_password_confirm = _post('vpn_password_confirm');
        $cert_validity_days = _post('cert_validity_days', 365);
        $api_username = _post('api_username');
        $api_password = _post('api_password');
        $api_port = _post('api_port', 8728);
        $coordinates = _post('coordinates');
        $coverage = _post('coverage');

        $msg = '';
        
        // Validate name
        if (Validator::Length($name, 30, 1) == false) {
            $msg .= 'Name should be between 1 to 30 characters' . '<br>';
        }
        if (strtolower($name) == 'radius') {
            $msg .= '<b>Radius</b> name is reserved<br>';
        }

        // Check if name already exists
        $existingRouter = ORM::for_table('tbl_routers')->where('name', $name)->find_one();
        if ($existingRouter) {
            $msg .= Lang::T('Router name already exists') . '<br>';
        }

        // Validate VPN credentials
        if (empty($vpn_username) || empty($vpn_password)) {
            $msg .= Lang::T('VPN username and password are required') . '<br>';
        }

        if ($vpn_password !== $vpn_password_confirm) {
            $msg .= Lang::T('VPN password and confirmation do not match') . '<br>';
        }

        // Validate API credentials
        if (empty($api_username) || empty($api_password)) {
            $msg .= Lang::T('API username and password are required') . '<br>';
        }

        if ($msg == '') {
            try {
                // Call VPNConfigurationService to create remote router configuration
                $vpnService = new VPNConfigurationService();
                
                $routerData = [
                    'name' => $name,
                    'description' => $description,
                    'vpn_username' => $vpn_username,
                    'vpn_password' => $vpn_password,
                    'cert_validity_days' => $cert_validity_days,
                    'api_username' => $api_username,
                    'api_password' => $api_password,
                    'api_port' => $api_port,
                    'coordinates' => $coordinates,
                    'coverage' => $coverage,
                    'admin_id' => $admin['id']
                ];

                $result = $vpnService->createRemoteRouterConfiguration($routerData);

                // Log success
                VPNAuditLog::logAction(
                    $result['router_id'],
                    'remote_router_created',
                    $admin['id'],
                    json_encode(['router_name' => $name]),
                    'success'
                );

                // Redirect to config-summary page
                r2(getUrl('routers/config-summary/' . $result['router_id']), 's', Lang::T('Remote router configured successfully'));

            } catch (VPNException $e) {
                // Log failure
                VPNAuditLog::logAction(
                    0,
                    'remote_router_creation_failed',
                    $admin['id'],
                    json_encode([
                        'router_name' => $name,
                        'error' => $e->getMessage(),
                        'error_code' => $e->getErrorCode()
                    ]),
                    'failed'
                );

                // Display user-friendly error message
                $errorMsg = 'Configuration failed: ' . $e->getMessage();
                r2(getUrl('routers/add'), 'e', $errorMsg);
            } catch (Exception $e) {
                // Handle unexpected errors
                VPNAuditLog::logAction(
                    0,
                    'remote_router_creation_failed',
                    $admin['id'],
                    json_encode([
                        'router_name' => $name,
                        'error' => $e->getMessage()
                    ]),
                    'failed'
                );

                r2(getUrl('routers/add'), 'e', 'An unexpected error occurred: ' . $e->getMessage());
            }
        } else {
            r2(getUrl('routers/add'), 'e', $msg);
        }
        break;

    case 'config-summary':
        $id = $routes['2'];
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            r2(getUrl('routers/list'), 'e', Lang::T('Router not found'));
        }

        // Verify this is a remote router
        if ($router['connection_type'] !== 'remote') {
            r2(getUrl('routers/list'), 'e', Lang::T('This is not a remote router'));
        }

        // Retrieve certificate information
        $certificate = ORM::for_table('tbl_vpn_certificates')
            ->where('router_id', $id)
            ->order_by_desc('id')
            ->find_one();

        // Calculate certificate days remaining
        $daysRemaining = null;
        if ($certificate && $certificate['expiry_date']) {
            $expiryDate = new DateTime($certificate['expiry_date']);
            $now = new DateTime();
            $interval = $now->diff($expiryDate);
            $daysRemaining = $interval->days * ($interval->invert ? -1 : 1);
        }

        // Retrieve VPN server configuration from config
        global $config;
        $vpnConfig = [
            'server_ip' => $config['vpn_server_ip'] ?? '',
            'server_port' => $config['vpn_server_port'] ?? 1194,
            'protocol' => $config['vpn_protocol'] ?? 'tcp',
            'subnet' => $config['vpn_subnet'] ?? '10.8.0.0/24'
        ];

        // Calculate expected VPN IP address (if not already assigned)
        $expectedVpnIp = $router['vpn_ip'];
        if (empty($expectedVpnIp)) {
            try {
                $vpnService = new VPNConfigurationService();
                $expectedVpnIp = $vpnService->getNextAvailableVPNIP();
            } catch (Exception $e) {
                $expectedVpnIp = 'Not assigned yet';
            }
        }

        // Assign data to Smarty template
        $ui->assign('router', $router);
        $ui->assign('certificate', $certificate);
        $ui->assign('days_remaining', $daysRemaining);
        $ui->assign('vpn_config', $vpnConfig);
        $ui->assign('expected_vpn_ip', $expectedVpnIp);

        // Display template
        $ui->display('admin/routers/config-summary.tpl');
        break;

    case 'download-config':
        $id = $routes['2'];
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            r2(getUrl('routers/list'), 'e', Lang::T('Router not found'));
        }

        // Verify config_package_path exists
        $configPath = $router['config_package_path'];
        if (empty($configPath) || !file_exists($configPath)) {
            r2(getUrl('routers/config-summary/' . $id), 'e', Lang::T('Configuration package not found'));
        }

        // Set appropriate headers for ZIP file download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($configPath) . '"');
        header('Content-Length: ' . filesize($configPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        // Stream ZIP file to browser
        readfile($configPath);
        exit;
        break;

    case 'download-file':
        $id = $routes['2'];
        $fileType = _get('type');
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            r2(getUrl('routers/list'), 'e', Lang::T('Router not found'));
        }

        // Validate file type parameter
        $validTypes = ['ovpn', 'ca', 'cert', 'key', 'script'];
        if (!in_array($fileType, $validTypes)) {
            r2(getUrl('routers/config-summary/' . $id), 'e', Lang::T('Invalid file type'));
        }

        // Construct file path based on certificate_path and file type
        $certPath = $router['certificate_path'];
        if (empty($certPath)) {
            r2(getUrl('routers/config-summary/' . $id), 'e', Lang::T('Certificate path not found'));
        }

        $filePath = '';
        $fileName = '';
        $contentType = 'application/octet-stream';

        switch ($fileType) {
            case 'ovpn':
                $filePath = $certPath . '/' . $router['vpn_username'] . '.ovpn';
                $fileName = $router['vpn_username'] . '.ovpn';
                $contentType = 'application/x-openvpn-profile';
                break;
            case 'ca':
                $filePath = $certPath . '/ca.crt';
                $fileName = 'ca.crt';
                $contentType = 'application/x-x509-ca-cert';
                break;
            case 'cert':
                $filePath = $certPath . '/' . $router['vpn_username'] . '.crt';
                $fileName = $router['vpn_username'] . '.crt';
                $contentType = 'application/x-x509-user-cert';
                break;
            case 'key':
                $filePath = $certPath . '/' . $router['vpn_username'] . '.key';
                $fileName = $router['vpn_username'] . '.key';
                $contentType = 'application/x-pem-file';
                break;
            case 'script':
                $filePath = $certPath . '/mikrotik-setup.rsc';
                $fileName = 'mikrotik-setup.rsc';
                $contentType = 'text/plain';
                break;
        }

        // Verify file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            r2(getUrl('routers/config-summary/' . $id), 'e', Lang::T('File not found or not readable'));
        }

        // Set appropriate headers for file download
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        // Stream file to browser
        readfile($filePath);
        exit;
        break;

    case 'test-vpn-connection':
        $id = $routes['2'];
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            echo json_encode(['success' => false, 'message' => 'Router not found']);
            exit;
        }

        // Extract VPN IP, API username, API password, and API port
        $vpnIp = $router['vpn_ip'];
        $apiUsername = $router['username'];
        $apiPassword = $router['password'];
        $apiPort = 8728; // Default MikroTik API port

        if (empty($vpnIp)) {
            echo json_encode(['success' => false, 'message' => 'VPN IP not assigned yet']);
            exit;
        }

        try {
            // Call VPNConfigurationService::testVPNConnection
            $vpnService = new VPNConfigurationService();
            $testResults = $vpnService->testVPNConnection($vpnIp, $apiUsername, $apiPassword, $apiPort);

            // Return JSON response with test results
            echo json_encode([
                'success' => true,
                'results' => $testResults
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
        break;

    case 'renew-certificate':
        $id = $routes['2'];
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            r2(getUrl('routers/list'), 'e', Lang::T('Router not found'));
        }

        // Verify admin has permission
        if (!VPNAccessControl::canRenewCertificates($admin)) {
            r2(getUrl('routers/config-summary/' . $id), 'e', Lang::T('You do not have permission to renew certificates'));
        }

        try {
            global $config;
            
            // Call CertificateManager::generateClientCertificate with existing VPN username
            $certManager = new CertificateManager($config);
            $vpnUsername = $router['vpn_username'];
            $certValidityDays = $config['vpn_cert_validity_days'] ?? 365;
            
            $certResult = $certManager->generateClientCertificate($vpnUsername, $certValidityDays);

            // Update certificate_expiry in tbl_routers
            $expiryDate = new DateTime();
            $expiryDate->add(new DateInterval('P' . $certValidityDays . 'D'));
            $router->certificate_expiry = $expiryDate->format('Y-m-d');
            $router->save();

            // Update tbl_vpn_certificates
            $certificate = ORM::for_table('tbl_vpn_certificates')
                ->where('router_id', $id)
                ->order_by_desc('id')
                ->find_one();
            
            if ($certificate) {
                $certificate->expiry_date = $expiryDate->format('Y-m-d H:i:s');
                $certificate->issued_date = date('Y-m-d H:i:s');
                $certificate->status = 'active';
                $certificate->save();
            }

            // Regenerate OVPN config file with new certificate
            $openVPNService = new OpenVPNService($config);
            $serverIp = $config['vpn_server_ip'] ?? '';
            $serverPort = $config['vpn_server_port'] ?? 1194;
            $certPath = $router['certificate_path'];
            
            $ovpnPath = $openVPNService->generateOVPNConfig($vpnUsername, $serverIp, $serverPort, $certPath);

            // Regenerate MikroTik script with new certificate
            $scriptGenerator = new MikroTikScriptGenerator();
            $scriptConfig = [
                'vpn_username' => $vpnUsername,
                'vpn_password' => $router['vpn_password_hash'], // This should be decrypted
                'server_ip' => $serverIp,
                'server_port' => $serverPort,
                'api_username' => $router['username'],
                'api_password' => $router['password'],
                'api_port' => 8728,
                'cert_path' => $certPath
            ];
            
            $scriptPath = $scriptGenerator->generateRouterOSScript($scriptConfig);

            // Create new configuration package ZIP
            $files = [
                'ovpn' => $ovpnPath,
                'script' => $scriptPath,
                'ca' => $certPath . '/ca.crt',
                'cert' => $certPath . '/' . $vpnUsername . '.crt',
                'key' => $certPath . '/' . $vpnUsername . '.key'
            ];
            
            $packagePath = $scriptGenerator->createConfigurationPackage($id, $files);
            $router->config_package_path = $packagePath;
            $router->save();

            // Log action to tbl_vpn_audit_log
            VPNAuditLog::logAction(
                $id,
                'certificate_renewed',
                $admin['id'],
                json_encode(['vpn_username' => $vpnUsername, 'validity_days' => $certValidityDays]),
                'success'
            );

            // Redirect to config-summary page with success message
            r2(getUrl('routers/config-summary/' . $id), 's', Lang::T('Certificate renewed successfully'));

        } catch (Exception $e) {
            // Log failure
            VPNAuditLog::logAction(
                $id,
                'certificate_renewal_failed',
                $admin['id'],
                json_encode(['error' => $e->getMessage()]),
                'failed'
            );

            r2(getUrl('routers/config-summary/' . $id), 'e', 'Certificate renewal failed: ' . $e->getMessage());
        }
        break;

    case 'vpn-status':
        // Retrieve all remote routers from database
        $remoteRouters = ORM::for_table('tbl_routers')
            ->where('connection_type', 'remote')
            ->find_many();

        $routerStats = [];
        foreach ($remoteRouters as $router) {
            // Get current VPN status and last check time
            $stats = [
                'id' => $router['id'],
                'name' => $router['name'],
                'vpn_ip' => $router['vpn_ip'],
                'ovpn_status' => $router['ovpn_status'],
                'last_vpn_check' => $router['last_vpn_check'],
                'certificate_expiry' => $router['certificate_expiry']
            ];

            // Calculate connection statistics using VPNMetrics
            try {
                $stats['uptime_percentage'] = VPNMetrics::getConnectionUptime($router['id'], 7);
                $stats['data_transferred'] = VPNMetrics::getDataTransferred($router['id'], 7);
            } catch (Exception $e) {
                $stats['uptime_percentage'] = 0;
                $stats['data_transferred'] = ['sent' => 0, 'received' => 0];
            }

            $routerStats[] = $stats;
        }

        // Calculate system-wide statistics
        try {
            $systemStats = VPNMetrics::getSystemWideStats();
        } catch (Exception $e) {
            $systemStats = [
                'total_routers' => count($remoteRouters),
                'connected' => 0,
                'disconnected' => 0,
                'errors' => 0,
                'connection_rate' => 0
            ];
        }

        // Assign data to Smarty template
        $ui->assign('router_stats', $routerStats);
        $ui->assign('system_stats', $systemStats);

        // Display template
        $ui->display('admin/routers/vpn-status.tpl');
        break;

    case 'vpn-logs':
        $id = $routes['2'];
        
        // Retrieve router record by ID
        $router = ORM::for_table('tbl_routers')->find_one($id);
        if (!$router) {
            r2(getUrl('routers/list'), 'e', Lang::T('Router not found'));
        }

        // Retrieve connection logs
        $days = _get('days', 7);
        $connectionLogs = VPNConnectionLog::getRouterHistory($id, $days);

        // Retrieve audit logs
        $limit = _get('limit', 50);
        $auditLogs = VPNAuditLog::getRouterLogs($id, $limit);

        // Assign data to Smarty template
        $ui->assign('router', $router);
        $ui->assign('connection_logs', $connectionLogs);
        $ui->assign('audit_logs', $auditLogs);
        $ui->assign('days', $days);

        // Display template
        $ui->display('admin/routers/vpn-logs.tpl');
        break;

    case 'edit':
        $id  = $routes['2'];
        $d = ORM::for_table('tbl_routers')->find_one($id);
        if (!$d) {
            $d = ORM::for_table('tbl_routers')->where_equal('name', _get('name'))->find_one();
        }
        $ui->assign('xheader', $leafletpickerHeader);
        if ($d) {
            $ui->assign('d', $d);
            
            // Check if router is remote type
            if ($d['connection_type'] === 'remote') {
                // Get VPN status information
                $vpnStatus = [
                    'status' => $d['ovpn_status'],
                    'last_check' => $d['last_vpn_check'],
                    'vpn_ip' => $d['vpn_ip']
                ];
                
                // Get certificate information
                $certificate = ORM::for_table('tbl_vpn_certificates')
                    ->where('router_id', $id)
                    ->order_by_desc('id')
                    ->find_one();
                
                $certInfo = null;
                if ($certificate) {
                    $expiryDate = new DateTime($certificate['expiry_date']);
                    $now = new DateTime();
                    $interval = $now->diff($expiryDate);
                    $daysRemaining = $interval->days * ($interval->invert ? -1 : 1);
                    
                    $certInfo = [
                        'expiry_date' => $certificate['expiry_date'],
                        'days_remaining' => $daysRemaining,
                        'status' => $certificate['status']
                    ];
                }
                
                $ui->assign('vpn_status', $vpnStatus);
                $ui->assign('cert_info', $certInfo);
                $ui->assign('is_remote', true);
            } else {
                $ui->assign('is_remote', false);
            }
            
            run_hook('view_router_edit'); #HOOK
            $ui->display('admin/routers/edit.tpl');
        } else {
            r2(getUrl('routers/list'), 'e', Lang::T('Account Not Found'));
        }
        break;

    case 'delete':
        $id  = $routes['2'];
        run_hook('router_delete'); #HOOK
        $d = ORM::for_table('tbl_routers')->find_one($id);
        if ($d) {
            // Check if router is remote type
            if ($d['connection_type'] === 'remote') {
                try {
                    global $config;
                    
                    // Remove VPN user
                    $openVPNService = new OpenVPNService($config);
                    $openVPNService->removeVPNUser($d['vpn_username']);
                    
                    // Revoke certificate
                    $certManager = new CertificateManager($config);
                    $certManager->revokeCertificate($d['vpn_username']);
                    
                    // Delete related records from tbl_vpn_certificates
                    $certificates = ORM::for_table('tbl_vpn_certificates')
                        ->where('router_id', $id)
                        ->find_many();
                    foreach ($certificates as $cert) {
                        $cert->delete();
                    }
                    
                    // Delete related records from tbl_vpn_connection_logs
                    $connectionLogs = ORM::for_table('tbl_vpn_connection_logs')
                        ->where('router_id', $id)
                        ->find_many();
                    foreach ($connectionLogs as $log) {
                        $log->delete();
                    }
                    
                    // Delete related records from tbl_vpn_audit_log
                    $auditLogs = ORM::for_table('tbl_vpn_audit_log')
                        ->where('router_id', $id)
                        ->find_many();
                    foreach ($auditLogs as $log) {
                        $log->delete();
                    }
                    
                    // Delete configuration package files from storage
                    if (!empty($d['config_package_path']) && file_exists($d['config_package_path'])) {
                        unlink($d['config_package_path']);
                    }
                    
                    // Delete certificate directory
                    if (!empty($d['certificate_path']) && is_dir($d['certificate_path'])) {
                        $files = glob($d['certificate_path'] . '/*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                            }
                        }
                        rmdir($d['certificate_path']);
                    }
                    
                    // Log deletion
                    VPNAuditLog::logAction(
                        $id,
                        'remote_router_deleted',
                        $admin['id'],
                        json_encode(['router_name' => $d['name'], 'vpn_username' => $d['vpn_username']]),
                        'success'
                    );
                    
                } catch (Exception $e) {
                    // Log error but continue with deletion
                    VPNAuditLog::logAction(
                        $id,
                        'remote_router_deletion_error',
                        $admin['id'],
                        json_encode(['router_name' => $d['name'], 'error' => $e->getMessage()]),
                        'failed'
                    );
                }
            }
            
            $d->delete();
            r2(getUrl('routers/list'), 's', Lang::T('Data Deleted Successfully'));
        }
        break;

    case 'add-post':
        $name = _post('name');
        $ip_address = _post('ip_address');
        $username = _post('username');
        $password = _post('password');
        $description = _post('description');
        $enabled = _post('enabled');

        $msg = '';
        if (Validator::Length($name, 30, 1) == false) {
            $msg .= 'Name should be between 1 to 30 characters' . '<br>';
        }
        if($enabled || _post("testIt")){
            if ($ip_address == '' or $username == '') {
                $msg .= Lang::T('All field is required') . '<br>';
            }

            $d = ORM::for_table('tbl_routers')->where('ip_address', $ip_address)->find_one();
            if ($d) {
                $msg .= Lang::T('IP Router Already Exist') . '<br>';
            }
        }
        if (strtolower($name) == 'radius') {
            $msg .= '<b>Radius</b> name is reserved<br>';
        }

        if ($msg == '') {
            run_hook('add_router'); #HOOK
            if (_post("testIt")) {
                (new MikrotikHotspot())->getClient($ip_address, $username, $password);
            }
            $d = ORM::for_table('tbl_routers')->create();
            $d->name = $name;
            $d->ip_address = $ip_address;
            $d->username = $username;
            $d->password = $password;
            $d->description = $description;
            $d->enabled = $enabled;
            $d->save();

            r2(getUrl('routers/edit/') . $d->id(), 's', Lang::T('Data Created Successfully'));
        } else {
            r2(getUrl('routers/add'), 'e', $msg);
        }
        break;


    case 'edit-post':
        $name = _post('name');
        $ip_address = _post('ip_address');
        $username = _post('username');
        $password = _post('password');
        $description = _post('description');
        $coordinates = _post('coordinates');
        $coverage = _post('coverage');
        $enabled = $_POST['enabled'];
        $msg = '';
        if (Validator::Length($name, 30, 4) == false) {
            $msg .= 'Name should be between 5 to 30 characters' . '<br>';
        }

        $id = _post('id');
        $d = ORM::for_table('tbl_routers')->find_one($id);
        if ($d) {
        } else {
            $msg .= Lang::T('Data Not Found') . '<br>';
        }

        // Check if this is a remote router
        $isRemote = ($d && $d['connection_type'] === 'remote');
        
        if (!$isRemote) {
            // For local routers, validate IP and username
            if($enabled || _post("testIt")){
                if ($ip_address == '' or $username == '') {
                    $msg .= Lang::T('All field is required') . '<br>';
                }
            }
        } else {
            // For remote routers, handle VPN IP update
            $vpn_ip = _post('vpn_ip');
            if (!empty($vpn_ip) && empty($d['vpn_ip'])) {
                // Validate VPN IP format
                if (!filter_var($vpn_ip, FILTER_VALIDATE_IP)) {
                    $msg .= 'Invalid VPN IP address format<br>';
                }
            }
        }

        if ($d['name'] != $name) {
            $c = ORM::for_table('tbl_routers')->where('name', $name)->where_not_equal('id', $id)->find_one();
            if ($c) {
                $msg .= 'Name Already Exists<br>';
            }
        }
        $oldname = $d['name'];

        if (!$isRemote) {
            if($enabled || _post("testIt")){
                if ($d['ip_address'] != $ip_address) {
                    $c = ORM::for_table('tbl_routers')->where('ip_address', $ip_address)->where_not_equal('id', $id)->find_one();
                    if ($c) {
                        $msg .= 'IP Already Exists<br>';
                    }
                }
            }
        }

        if (strtolower($name) == 'radius') {
            $msg .= '<b>Radius</b> name is reserved<br>';
        }

        if ($msg == '') {
            run_hook('router_edit'); #HOOK
            if (!$isRemote && _post("testIt")) {
                (new MikrotikHotspot())->getClient($ip_address, $username, $password);
            }
            $d->name = $name;
            
            if ($isRemote) {
                // For remote routers, update VPN IP if provided
                $vpn_ip = _post('vpn_ip');
                if (!empty($vpn_ip) && empty($d['vpn_ip'])) {
                    $d->vpn_ip = $vpn_ip;
                }
                // Update API credentials if changed
                if (!empty($username)) {
                    $d->username = $username;
                }
                if (!empty($password)) {
                    $d->password = $password;
                }
            } else {
                // For local routers, update IP and credentials
                $d->ip_address = $ip_address;
                $d->username = $username;
                $d->password = $password;
            }
            
            $d->description = $description;
            $d->coordinates = $coordinates;
            $d->coverage = $coverage;
            $d->enabled = $enabled;
            $d->save();
            if ($name != $oldname) {
                $p = ORM::for_table('tbl_plans')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
                $p = ORM::for_table('tbl_payment_gateway')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
                $p = ORM::for_table('tbl_pool')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
                $p = ORM::for_table('tbl_transactions')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
                $p = ORM::for_table('tbl_user_recharges')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
                $p = ORM::for_table('tbl_voucher')->where('routers', $oldname)->find_result_set();
                $p->set('routers', $name);
                $p->save();
            }
            r2(getUrl('routers/list'), 's', Lang::T('Data Updated Successfully'));
        } else {
            r2(getUrl('routers/edit/') . $id, 'e', $msg);
        }
        break;

    default:

        $name = _post('name');
        $name = _post('name');
        $query = ORM::for_table('tbl_routers')->order_by_desc('id');
        if ($name != '') {
            $query->where_like('name', '%' . $name . '%');
        }
        $d = Paginator::findMany($query, ['name' => $name]);
        $ui->assign('d', $d);
        run_hook('view_list_routers'); #HOOK
        $ui->display('admin/routers/list.tpl');
        break;
}
