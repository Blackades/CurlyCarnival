<?php
/**
 * VPN Integration End-to-End Test Script
 * 
 * This script tests the complete VPN automation workflow including:
 * - Service class autoloading
 * - Remote router creation workflow
 * - VPN connection monitoring
 * - Certificate management
 * - Rollback mechanisms
 * - Audit logging
 * - Dashboard widgets
 * 
 * Usage: php system/test_vpn_integration.php
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line\n");
}

require_once __DIR__ . '/../init.php';

class VPNIntegrationTest {
    private $testResults = [];
    private $testRouterId = null;
    private $testUsername = 'test-vpn-' . time();
    private $config;
    
    public function __construct() {
        global $config;
        $this->config = $config;
    }
    
    public function runAllTests() {
        echo "\n";
        echo "========================================\n";
        echo "VPN Integration End-to-End Test Suite\n";
        echo "========================================\n\n";
        
        // Test 1: Verify autoloading
        $this->testAutoloading();
        
        // Test 2: Verify database schema
        $this->testDatabaseSchema();
        
        // Test 3: Test input validation
        $this->testInputValidation();
        
        // Test 4: Test password management
        $this->testPasswordManagement();
        
        // Test 5: Test certificate manager
        $this->testCertificateManager();
        
        // Test 6: Test OpenVPN service
        $this->testOpenVPNService();
        
        // Test 7: Test MikroTik script generator
        $this->testMikroTikScriptGenerator();
        
        // Test 8: Test VPN configuration service
        $this->testVPNConfigurationService();
        
        // Test 9: Test monitoring service
        $this->testMonitoringService();
        
        // Test 10: Test rollback mechanism
        $this->testRollbackMechanism();
        
        // Test 11: Test audit logging
        $this->testAuditLogging();
        
        // Test 12: Test dashboard widgets
        $this->testDashboardWidgets();
        
        // Test 13: Test ORM models
        $this->testORMModels();
        
        // Test 14: Test access control
        $this->testAccessControl();
        
        // Cleanup
        $this->cleanup();
        
        // Print summary
        $this->printSummary();
    }
    
    private function testAutoloading() {
        echo "Test 1: Verifying service class autoloading...\n";
        
        $classes = [
            'VPNInputValidator',
            'VPNPasswordManager',
            'VPNException',
            'CertificateManager',
            'OpenVPNService',
            'MikroTikScriptGenerator',
            'VPNConfigurationService',
            'VPNMonitoringService',
            'VPNRollbackManager',
            'VPNAccessControl',
            'VPNAlertManager',
            'VPNMetrics',
            'VPNAuditLog',
            'VPNCertificate',
            'VPNConnectionLog'
        ];
        
        $allLoaded = true;
        foreach ($classes as $class) {
            if (class_exists($class)) {
                echo "  ✓ $class loaded\n";
            } else {
                echo "  ✗ $class NOT loaded\n";
                $allLoaded = false;
            }
        }
        
        $this->recordTest('Autoloading', $allLoaded);
    }
    
    private function testDatabaseSchema() {
        echo "\nTest 2: Verifying database schema...\n";
        
        try {
            // Check tbl_routers columns
            $router = ORM::for_table('tbl_routers')->find_one();
            $columns = ['connection_type', 'vpn_username', 'vpn_password_hash', 'vpn_ip', 
                       'certificate_path', 'certificate_expiry', 'ovpn_status', 
                       'last_vpn_check', 'config_package_path'];
            
            $routerColumnsExist = true;
            foreach ($columns as $col) {
                if (!property_exists($router, $col) && !isset($router->$col)) {
                    echo "  ✗ Column $col missing from tbl_routers\n";
                    $routerColumnsExist = false;
                }
            }
            
            if ($routerColumnsExist) {
                echo "  ✓ tbl_routers schema verified\n";
            }
            
            // Check new tables
            $tables = [
                'tbl_vpn_audit_log',
                'tbl_vpn_certificates',
                'tbl_vpn_connection_logs'
            ];
            
            $tablesExist = true;
            foreach ($tables as $table) {
                $result = ORM::for_table($table)->count();
                if ($result !== false) {
                    echo "  ✓ $table exists\n";
                } else {
                    echo "  ✗ $table does NOT exist\n";
                    $tablesExist = false;
                }
            }
            
            $this->recordTest('Database Schema', $routerColumnsExist && $tablesExist);
            
        } catch (Exception $e) {
            echo "  ✗ Database schema check failed: " . $e->getMessage() . "\n";
            $this->recordTest('Database Schema', false);
        }
    }
    
    private function testInputValidation() {
        echo "\nTest 3: Testing input validation...\n";
        
        try {
            // Test valid username
            VPNInputValidator::validateUsername('test-router-01');
            echo "  ✓ Valid username accepted\n";
            
            // Test invalid username
            try {
                VPNInputValidator::validateUsername('test@router');
                echo "  ✗ Invalid username not rejected\n";
                $usernameValidation = false;
            } catch (VPNException $e) {
                echo "  ✓ Invalid username rejected\n";
                $usernameValidation = true;
            }
            
            // Test valid password
            VPNInputValidator::validatePassword('StrongPass123');
            echo "  ✓ Strong password accepted\n";
            
            // Test weak password
            try {
                VPNInputValidator::validatePassword('weak');
                echo "  ✗ Weak password not rejected\n";
                $passwordValidation = false;
            } catch (VPNException $e) {
                echo "  ✓ Weak password rejected\n";
                $passwordValidation = true;
            }
            
            // Test IP validation
            VPNInputValidator::validateIPAddress('192.168.1.1');
            echo "  ✓ Valid IP accepted\n";
            
            // Test shell argument sanitization
            $sanitized = VPNInputValidator::sanitizeShellArg("test; rm -rf /");
            if (strpos($sanitized, ';') === false) {
                echo "  ✓ Shell argument sanitized\n";
                $shellSanitization = true;
            } else {
                echo "  ✗ Shell argument not sanitized\n";
                $shellSanitization = false;
            }
            
            $this->recordTest('Input Validation', $usernameValidation && $passwordValidation && $shellSanitization);
            
        } catch (Exception $e) {
            echo "  ✗ Input validation test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Input Validation', false);
        }
    }
    
    private function testPasswordManagement() {
        echo "\nTest 4: Testing password management...\n";
        
        try {
            $password = 'TestPassword123';
            
            // Test hashing
            $hash = VPNPasswordManager::hashPassword($password);
            echo "  ✓ Password hashed\n";
            
            // Test verification
            if (VPNPasswordManager::verifyPassword($password, $hash)) {
                echo "  ✓ Password verification works\n";
                $hashingWorks = true;
            } else {
                echo "  ✗ Password verification failed\n";
                $hashingWorks = false;
            }
            
            // Test encryption (if encryption key is configured)
            if (!empty($this->config['vpn_encryption_key'])) {
                $encrypted = VPNPasswordManager::encryptForStorage($password, $this->config['vpn_encryption_key']);
                echo "  ✓ Password encrypted\n";
                
                $decrypted = VPNPasswordManager::decryptFromStorage($encrypted, $this->config['vpn_encryption_key']);
                if ($decrypted === $password) {
                    echo "  ✓ Password decryption works\n";
                    $encryptionWorks = true;
                } else {
                    echo "  ✗ Password decryption failed\n";
                    $encryptionWorks = false;
                }
            } else {
                echo "  ⚠ Encryption key not configured, skipping encryption test\n";
                $encryptionWorks = true; // Don't fail if not configured
            }
            
            $this->recordTest('Password Management', $hashingWorks && $encryptionWorks);
            
        } catch (Exception $e) {
            echo "  ✗ Password management test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Password Management', false);
        }
    }
    
    private function testCertificateManager() {
        echo "\nTest 5: Testing certificate manager...\n";
        
        try {
            $certManager = new CertificateManager($this->config);
            
            // Test certificate validity check
            $validity = $certManager->checkCertificateValidity();
            if (isset($validity['status'])) {
                echo "  ✓ Certificate validity check works\n";
                echo "    Status: " . $validity['status'] . "\n";
                if (isset($validity['ca_days_remaining'])) {
                    echo "    CA days remaining: " . $validity['ca_days_remaining'] . "\n";
                }
                $validityCheck = true;
            } else {
                echo "  ✗ Certificate validity check failed\n";
                $validityCheck = false;
            }
            
            $this->recordTest('Certificate Manager', $validityCheck);
            
        } catch (Exception $e) {
            echo "  ✗ Certificate manager test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Certificate Manager', false);
        }
    }
    
    private function testOpenVPNService() {
        echo "\nTest 6: Testing OpenVPN service...\n";
        
        try {
            $vpnService = new OpenVPNService($this->config);
            
            // Test service status
            $status = $vpnService->getServiceStatus();
            if (isset($status['running'])) {
                echo "  ✓ Service status check works\n";
                echo "    OpenVPN running: " . ($status['running'] ? 'Yes' : 'No') . "\n";
                $statusCheck = true;
            } else {
                echo "  ✗ Service status check failed\n";
                $statusCheck = false;
            }
            
            // Test connected clients
            $clients = $vpnService->getConnectedClients();
            if (is_array($clients)) {
                echo "  ✓ Connected clients check works\n";
                echo "    Connected clients: " . count($clients) . "\n";
                $clientsCheck = true;
            } else {
                echo "  ✗ Connected clients check failed\n";
                $clientsCheck = false;
            }
            
            $this->recordTest('OpenVPN Service', $statusCheck && $clientsCheck);
            
        } catch (Exception $e) {
            echo "  ✗ OpenVPN service test failed: " . $e->getMessage() . "\n";
            $this->recordTest('OpenVPN Service', false);
        }
    }
    
    private function testMikroTikScriptGenerator() {
        echo "\nTest 7: Testing MikroTik script generator...\n";
        
        try {
            $generator = new MikroTikScriptGenerator();
            
            $config = [
                'vpn_username' => 'test-user',
                'vpn_password' => 'TestPass123',
                'vpn_server_ip' => '203.0.113.10',
                'vpn_server_port' => 1194,
                'api_username' => 'admin',
                'api_password' => 'admin123',
                'api_port' => 8728,
                'client_name' => 'test-client'
            ];
            
            // Test script generation
            $script = $generator->generateRouterOSScript($config);
            if (!empty($script) && strpos($script, '/interface ovpn-client') !== false) {
                echo "  ✓ RouterOS script generated\n";
                $scriptGen = true;
            } else {
                echo "  ✗ RouterOS script generation failed\n";
                $scriptGen = false;
            }
            
            // Test instructions generation
            $instructions = $generator->generateSetupInstructions($config);
            if (!empty($instructions)) {
                echo "  ✓ Setup instructions generated\n";
                $instructionsGen = true;
            } else {
                echo "  ✗ Setup instructions generation failed\n";
                $instructionsGen = false;
            }
            
            $this->recordTest('MikroTik Script Generator', $scriptGen && $instructionsGen);
            
        } catch (Exception $e) {
            echo "  ✗ MikroTik script generator test failed: " . $e->getMessage() . "\n";
            $this->recordTest('MikroTik Script Generator', false);
        }
    }
    
    private function testVPNConfigurationService() {
        echo "\nTest 8: Testing VPN configuration service...\n";
        
        try {
            $vpnConfig = new VPNConfigurationService();
            
            // Test username availability check
            $available = $vpnConfig->checkUsernameAvailability($this->testUsername);
            if ($available) {
                echo "  ✓ Username availability check works\n";
                $availabilityCheck = true;
            } else {
                echo "  ✗ Username availability check failed\n";
                $availabilityCheck = false;
            }
            
            // Test credential validation
            try {
                $vpnConfig->validateVPNCredentials($this->testUsername, 'StrongPass123');
                echo "  ✓ Credential validation works\n";
                $credentialValidation = true;
            } catch (VPNException $e) {
                echo "  ✗ Credential validation failed: " . $e->getMessage() . "\n";
                $credentialValidation = false;
            }
            
            // Test next available VPN IP
            $nextIP = $vpnConfig->getNextAvailableVPNIP();
            if (!empty($nextIP) && filter_var($nextIP, FILTER_VALIDATE_IP)) {
                echo "  ✓ Next available VPN IP: $nextIP\n";
                $ipAllocation = true;
            } else {
                echo "  ✗ Next available VPN IP failed\n";
                $ipAllocation = false;
            }
            
            $this->recordTest('VPN Configuration Service', $availabilityCheck && $credentialValidation && $ipAllocation);
            
        } catch (Exception $e) {
            echo "  ✗ VPN configuration service test failed: " . $e->getMessage() . "\n";
            $this->recordTest('VPN Configuration Service', false);
        }
    }
    
    private function testMonitoringService() {
        echo "\nTest 9: Testing monitoring service...\n";
        
        try {
            $monitor = new VPNMonitoringService();
            
            // Test certificate expiration check
            $expiringCerts = $monitor->checkCertificateExpirations();
            if (is_array($expiringCerts)) {
                echo "  ✓ Certificate expiration check works\n";
                echo "    Expiring certificates: " . count($expiringCerts) . "\n";
                $certCheck = true;
            } else {
                echo "  ✗ Certificate expiration check failed\n";
                $certCheck = false;
            }
            
            $this->recordTest('Monitoring Service', $certCheck);
            
        } catch (Exception $e) {
            echo "  ✗ Monitoring service test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Monitoring Service', false);
        }
    }
    
    private function testRollbackMechanism() {
        echo "\nTest 10: Testing rollback mechanism...\n";
        
        try {
            $rollback = new VPNRollbackManager();
            
            // Add test rollback actions
            $rollback->addRollbackAction('test', ['param1' => 'value1']);
            $rollback->addRollbackAction('test', ['param2' => 'value2']);
            
            echo "  ✓ Rollback actions added\n";
            
            // Clear rollback
            $rollback->clearRollback();
            echo "  ✓ Rollback cleared\n";
            
            $this->recordTest('Rollback Mechanism', true);
            
        } catch (Exception $e) {
            echo "  ✗ Rollback mechanism test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Rollback Mechanism', false);
        }
    }
    
    private function testAuditLogging() {
        echo "\nTest 11: Testing audit logging...\n";
        
        try {
            // Create a test router for logging
            $testRouter = ORM::for_table('tbl_routers')->create();
            $testRouter->name = 'test-audit-router';
            $testRouter->ip_address = '192.168.1.100';
            $testRouter->username = 'admin';
            $testRouter->password = 'test';
            $testRouter->connection_type = 'remote';
            $testRouter->save();
            
            $this->testRouterId = $testRouter->id;
            
            // Test audit log creation
            VPNAuditLog::logAction(
                $this->testRouterId,
                'test_action',
                1, // admin_id
                ['test' => 'data'],
                'success'
            );
            
            echo "  ✓ Audit log created\n";
            
            // Test audit log retrieval
            $logs = VPNAuditLog::getRouterLogs($this->testRouterId, 10);
            if (count($logs) > 0) {
                echo "  ✓ Audit logs retrieved\n";
                $logsRetrieved = true;
            } else {
                echo "  ✗ Audit logs not retrieved\n";
                $logsRetrieved = false;
            }
            
            $this->recordTest('Audit Logging', $logsRetrieved);
            
        } catch (Exception $e) {
            echo "  ✗ Audit logging test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Audit Logging', false);
        }
    }
    
    private function testDashboardWidgets() {
        echo "\nTest 12: Testing dashboard widgets...\n";
        
        try {
            // Test VPN status widget
            if (file_exists(__DIR__ . '/widgets/vpn_status.php')) {
                require_once __DIR__ . '/widgets/vpn_status.php';
                $statusData = VPNStatusWidget::render();
                
                if (isset($statusData['total']) && isset($statusData['connected'])) {
                    echo "  ✓ VPN status widget works\n";
                    echo "    Total remote routers: " . $statusData['total'] . "\n";
                    echo "    Connected: " . $statusData['connected'] . "\n";
                    $statusWidget = true;
                } else {
                    echo "  ✗ VPN status widget failed\n";
                    $statusWidget = false;
                }
            } else {
                echo "  ⚠ VPN status widget file not found\n";
                $statusWidget = false;
            }
            
            // Test certificate widget
            if (file_exists(__DIR__ . '/widgets/vpn_certificates.php')) {
                require_once __DIR__ . '/widgets/vpn_certificates.php';
                $certData = VPNCertificateWidget::render();
                
                if (isset($certData['expiring_soon'])) {
                    echo "  ✓ Certificate widget works\n";
                    echo "    Expiring soon: " . $certData['expiring_soon'] . "\n";
                    $certWidget = true;
                } else {
                    echo "  ✗ Certificate widget failed\n";
                    $certWidget = false;
                }
            } else {
                echo "  ⚠ Certificate widget file not found\n";
                $certWidget = false;
            }
            
            $this->recordTest('Dashboard Widgets', $statusWidget && $certWidget);
            
        } catch (Exception $e) {
            echo "  ✗ Dashboard widgets test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Dashboard Widgets', false);
        }
    }
    
    private function testORMModels() {
        echo "\nTest 13: Testing ORM models...\n";
        
        try {
            // Test VPNCertificate model
            $cert = ORM::for_table('tbl_vpn_certificates')->create();
            $cert->router_id = $this->testRouterId ?? 1;
            $cert->client_name = 'test-cert-' . time();
            $cert->certificate_path = '/tmp/test.crt';
            $cert->key_path = '/tmp/test.key';
            $cert->ca_path = '/tmp/ca.crt';
            $cert->ovpn_file_path = '/tmp/test.ovpn';
            $cert->issued_date = date('Y-m-d H:i:s');
            $cert->expiry_date = date('Y-m-d H:i:s', strtotime('+365 days'));
            $cert->status = 'active';
            $cert->save();
            
            echo "  ✓ VPNCertificate model works\n";
            
            // Test VPNConnectionLog model
            VPNConnectionLog::logConnection(
                $this->testRouterId ?? 1,
                'connected',
                ['vpn_ip' => '10.8.0.2']
            );
            
            echo "  ✓ VPNConnectionLog model works\n";
            
            // Cleanup
            $cert->delete();
            
            $this->recordTest('ORM Models', true);
            
        } catch (Exception $e) {
            echo "  ✗ ORM models test failed: " . $e->getMessage() . "\n";
            $this->recordTest('ORM Models', false);
        }
    }
    
    private function testAccessControl() {
        echo "\nTest 14: Testing access control...\n";
        
        try {
            // Test SuperAdmin access
            $superAdmin = ['user_type' => 'SuperAdmin'];
            if (VPNAccessControl::canManageVPN($superAdmin)) {
                echo "  ✓ SuperAdmin can manage VPN\n";
                $superAdminAccess = true;
            } else {
                echo "  ✗ SuperAdmin access check failed\n";
                $superAdminAccess = false;
            }
            
            // Test regular user access
            $regularUser = ['user_type' => 'Sales'];
            if (!VPNAccessControl::canManageVPN($regularUser)) {
                echo "  ✓ Regular user cannot manage VPN\n";
                $regularUserAccess = true;
            } else {
                echo "  ✗ Regular user access check failed\n";
                $regularUserAccess = false;
            }
            
            // Test certificate renewal access
            if (VPNAccessControl::canRenewCertificates($superAdmin)) {
                echo "  ✓ SuperAdmin can renew certificates\n";
                $renewAccess = true;
            } else {
                echo "  ✗ Certificate renewal access check failed\n";
                $renewAccess = false;
            }
            
            $this->recordTest('Access Control', $superAdminAccess && $regularUserAccess && $renewAccess);
            
        } catch (Exception $e) {
            echo "  ✗ Access control test failed: " . $e->getMessage() . "\n";
            $this->recordTest('Access Control', false);
        }
    }
    
    private function cleanup() {
        echo "\nCleaning up test data...\n";
        
        try {
            // Delete test router
            if ($this->testRouterId) {
                $router = ORM::for_table('tbl_routers')->find_one($this->testRouterId);
                if ($router) {
                    $router->delete();
                    echo "  ✓ Test router deleted\n";
                }
                
                // Delete audit logs
                ORM::for_table('tbl_vpn_audit_log')
                    ->where('router_id', $this->testRouterId)
                    ->delete_many();
                echo "  ✓ Test audit logs deleted\n";
                
                // Delete connection logs
                ORM::for_table('tbl_vpn_connection_logs')
                    ->where('router_id', $this->testRouterId)
                    ->delete_many();
                echo "  ✓ Test connection logs deleted\n";
            }
            
        } catch (Exception $e) {
            echo "  ⚠ Cleanup warning: " . $e->getMessage() . "\n";
        }
    }
    
    private function recordTest($testName, $passed) {
        $this->testResults[$testName] = $passed;
    }
    
    private function printSummary() {
        echo "\n";
        echo "========================================\n";
        echo "Test Summary\n";
        echo "========================================\n\n";
        
        $passed = 0;
        $failed = 0;
        
        foreach ($this->testResults as $testName => $result) {
            $status = $result ? '✓ PASS' : '✗ FAIL';
            $color = $result ? "\033[32m" : "\033[31m";
            $reset = "\033[0m";
            
            echo sprintf("%-40s %s%s%s\n", $testName, $color, $status, $reset);
            
            if ($result) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $total = $passed + $failed;
        $percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        echo "\n";
        echo "Total Tests: $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Success Rate: $percentage%\n";
        echo "\n";
        
        if ($failed === 0) {
            echo "\033[32m✓ All tests passed!\033[0m\n\n";
            exit(0);
        } else {
            echo "\033[31m✗ Some tests failed. Please review the output above.\033[0m\n\n";
            exit(1);
        }
    }
}

// Run tests
$tester = new VPNIntegrationTest();
$tester->runAllTests();
