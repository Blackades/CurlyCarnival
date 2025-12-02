<?php
/**
 * VPN Feature Database Rollback Script
 * 
 * This script removes all database schema changes made by the VPN feature migration.
 * Use this to cleanly uninstall the VPN feature.
 * 
 * WARNING: This will delete all VPN-related data including:
 * - VPN audit logs
 * - VPN certificates records
 * - VPN connection logs
 * - VPN configuration from routers
 * 
 * Usage: php system/rollback_vpn.php
 */

// Check if running from command line or web
if (php_sapi_name() !== 'cli') {
    // Running from web - require authentication
    session_start();
    if (!isset($_SESSION['aid']) || empty($_SESSION['aid'])) {
        die('Error: Authentication required. Please login as admin.');
    }
}

// Load configuration
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    die('Error: config.php not found. Please ensure phpnuxbill is properly installed.');
}

// Database connection
try {
    $db = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Confirmation prompt (only in CLI mode)
if (php_sapi_name() === 'cli') {
    echo "\n";
    echo str_repeat("=", 60) . "\n";
    echo "VPN FEATURE ROLLBACK\n";
    echo str_repeat("=", 60) . "\n";
    echo "\nWARNING: This will permanently delete:\n";
    echo "  - All VPN audit logs\n";
    echo "  - All VPN certificate records\n";
    echo "  - All VPN connection logs\n";
    echo "  - VPN configuration columns from tbl_routers\n";
    echo "\nThis action CANNOT be undone!\n\n";
    echo "Are you sure you want to continue? (yes/no): ";
    
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'yes') {
        echo "\nRollback cancelled.\n";
        exit(0);
    }
    echo "\n";
}

// Rollback queries (in reverse order of creation)
$rollbacks = [
    // Drop VPN tables
    "DROP TABLE IF EXISTS `tbl_vpn_connection_logs`",
    "DROP TABLE IF EXISTS `tbl_vpn_certificates`",
    "DROP TABLE IF EXISTS `tbl_vpn_audit_log`",
    
    // Remove VPN columns from tbl_routers
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `config_package_path`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `last_vpn_check`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `ovpn_status`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `certificate_expiry`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `certificate_path`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `vpn_ip`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `vpn_password_hash`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `vpn_username`",
    "ALTER TABLE `tbl_routers` DROP COLUMN IF EXISTS `connection_type`"
];

// Track rollback status
$success = 0;
$failed = 0;
$skipped = 0;
$errors = [];

echo "Starting VPN feature database rollback...\n";
echo str_repeat("=", 60) . "\n\n";

// Execute rollbacks
foreach ($rollbacks as $index => $query) {
    $rollbackNum = $index + 1;
    echo "[$rollbackNum/" . count($rollbacks) . "] Executing rollback...\n";
    
    try {
        $db->exec($query);
        $success++;
        echo "✓ Success\n\n";
    } catch (PDOException $e) {
        // Check if error is due to column/table not existing
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        
        if (strpos($errorMsg, "doesn't exist") !== false || 
            strpos($errorMsg, "check that column/key exists") !== false ||
            strpos($errorMsg, "Unknown column") !== false ||
            $errorCode == '42S02' || // Table doesn't exist
            $errorCode == '42000') { // Column doesn't exist
            $skipped++;
            echo "⊘ Skipped (doesn't exist)\n\n";
        } else {
            $failed++;
            $errors[] = [
                'rollback' => $rollbackNum,
                'error' => $errorMsg,
                'code' => $errorCode
            ];
            echo "✗ Failed: " . $errorMsg . "\n\n";
        }
    }
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "Rollback Summary:\n";
echo "  ✓ Successful: $success\n";
echo "  ⊘ Skipped: $skipped\n";
echo "  ✗ Failed: $failed\n";
echo str_repeat("=", 60) . "\n\n";

if ($failed > 0) {
    echo "Errors encountered:\n";
    foreach ($errors as $error) {
        echo "  Rollback #{$error['rollback']}: {$error['error']} (Code: {$error['code']})\n";
    }
    echo "\n";
    exit(1);
} else {
    echo "✓ VPN feature database rollback completed successfully!\n\n";
    
    // Verify tables are removed
    echo "Verifying database cleanup...\n";
    $tables = ['tbl_vpn_audit_log', 'tbl_vpn_certificates', 'tbl_vpn_connection_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            echo "  ✓ Table $table removed\n";
        } else {
            echo "  ✗ Table $table still exists\n";
        }
    }
    
    // Verify columns removed from tbl_routers
    echo "\nVerifying tbl_routers columns removed...\n";
    $columns = ['connection_type', 'vpn_username', 'vpn_password_hash', 'vpn_ip', 
                'certificate_path', 'certificate_expiry', 'ovpn_status', 
                'last_vpn_check', 'config_package_path'];
    $stmt = $db->query("DESCRIBE tbl_routers");
    $existingColumns = [];
    while ($row = $stmt->fetch()) {
        $existingColumns[] = $row['Field'];
    }
    
    $allRemoved = true;
    foreach ($columns as $column) {
        if (!in_array($column, $existingColumns)) {
            echo "  ✓ Column $column removed\n";
        } else {
            echo "  ✗ Column $column still exists\n";
            $allRemoved = false;
        }
    }
    
    if ($allRemoved) {
        echo "\n✓ All VPN feature components successfully removed!\n";
    } else {
        echo "\n⚠ Some components could not be removed. Please check manually.\n";
    }
    
    echo "\nRollback complete.\n";
    exit(0);
}
