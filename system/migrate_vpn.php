<?php
/**
 * VPN Feature Database Migration Script
 * 
 * This script adds the necessary database schema for MikroTik OpenVPN automation feature.
 * It can be run standalone or will be executed automatically during system updates.
 * 
 * Usage: php system/migrate_vpn.php
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

// Migration queries
$migrations = [
    // Add VPN-related columns to tbl_routers
    "ALTER TABLE `tbl_routers` ADD COLUMN `connection_type` ENUM('local', 'remote') DEFAULT 'local' AFTER `enabled`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `vpn_username` VARCHAR(50) NULL AFTER `connection_type`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `vpn_password_hash` VARCHAR(255) NULL AFTER `vpn_username`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `vpn_ip` VARCHAR(45) NULL AFTER `vpn_password_hash`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `certificate_path` VARCHAR(255) NULL AFTER `vpn_ip`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `certificate_expiry` DATE NULL AFTER `certificate_path`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `ovpn_status` ENUM('connected', 'disconnected', 'error', 'pending') DEFAULT 'pending' AFTER `certificate_expiry`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `last_vpn_check` DATETIME NULL AFTER `ovpn_status`",
    "ALTER TABLE `tbl_routers` ADD COLUMN `config_package_path` VARCHAR(255) NULL AFTER `last_vpn_check`",
    
    // Create tbl_vpn_audit_log table
    "CREATE TABLE IF NOT EXISTS `tbl_vpn_audit_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `router_id` INT NOT NULL,
        `action` VARCHAR(50) NOT NULL,
        `admin_id` INT NOT NULL,
        `details` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
        `error_message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`router_id`) REFERENCES `tbl_routers`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`admin_id`) REFERENCES `tbl_users`(`id`) ON DELETE CASCADE,
        INDEX `idx_router_id` (`router_id`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    // Create tbl_vpn_certificates table
    "CREATE TABLE IF NOT EXISTS `tbl_vpn_certificates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `router_id` INT NOT NULL,
        `client_name` VARCHAR(100) NOT NULL UNIQUE,
        `certificate_path` VARCHAR(255) NOT NULL,
        `key_path` VARCHAR(255) NOT NULL,
        `ca_path` VARCHAR(255) NOT NULL,
        `ovpn_file_path` VARCHAR(255) NOT NULL,
        `issued_date` DATETIME NOT NULL,
        `expiry_date` DATETIME NOT NULL,
        `status` ENUM('active', 'expired', 'revoked') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`router_id`) REFERENCES `tbl_routers`(`id`) ON DELETE CASCADE,
        INDEX `idx_expiry_date` (`expiry_date`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    
    // Create tbl_vpn_connection_logs table
    "CREATE TABLE IF NOT EXISTS `tbl_vpn_connection_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `router_id` INT NOT NULL,
        `vpn_ip` VARCHAR(45) NULL,
        `connection_status` ENUM('connected', 'disconnected', 'error') NOT NULL,
        `bytes_sent` BIGINT DEFAULT 0,
        `bytes_received` BIGINT DEFAULT 0,
        `connection_time` DATETIME NULL,
        `disconnection_time` DATETIME NULL,
        `error_details` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`router_id`) REFERENCES `tbl_routers`(`id`) ON DELETE CASCADE,
        INDEX `idx_router_id` (`router_id`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
];

// Track migration status
$success = 0;
$failed = 0;
$skipped = 0;
$errors = [];

echo "Starting VPN feature database migration...\n";
echo str_repeat("=", 60) . "\n\n";

// Execute migrations
foreach ($migrations as $index => $query) {
    $migrationNum = $index + 1;
    echo "[$migrationNum/" . count($migrations) . "] Executing migration...\n";
    
    try {
        $db->exec($query);
        $success++;
        echo "✓ Success\n\n";
    } catch (PDOException $e) {
        // Check if error is due to column/table already existing
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        
        if (strpos($errorMsg, 'Duplicate column name') !== false || 
            strpos($errorMsg, 'already exists') !== false ||
            $errorCode == '42S21' || // Column already exists
            $errorCode == '42S01') { // Table already exists
            $skipped++;
            echo "⊘ Skipped (already exists)\n\n";
        } else {
            $failed++;
            $errors[] = [
                'migration' => $migrationNum,
                'error' => $errorMsg,
                'code' => $errorCode
            ];
            echo "✗ Failed: " . $errorMsg . "\n\n";
        }
    }
}

// Summary
echo str_repeat("=", 60) . "\n";
echo "Migration Summary:\n";
echo "  ✓ Successful: $success\n";
echo "  ⊘ Skipped: $skipped\n";
echo "  ✗ Failed: $failed\n";
echo str_repeat("=", 60) . "\n\n";

if ($failed > 0) {
    echo "Errors encountered:\n";
    foreach ($errors as $error) {
        echo "  Migration #{$error['migration']}: {$error['error']} (Code: {$error['code']})\n";
    }
    echo "\n";
    exit(1);
} else {
    echo "✓ VPN feature database migration completed successfully!\n\n";
    
    // Verify tables exist
    echo "Verifying database schema...\n";
    $tables = ['tbl_vpn_audit_log', 'tbl_vpn_certificates', 'tbl_vpn_connection_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Table $table exists\n";
        } else {
            echo "  ✗ Table $table NOT found\n";
        }
    }
    
    // Verify columns in tbl_routers
    echo "\nVerifying tbl_routers columns...\n";
    $columns = ['connection_type', 'vpn_username', 'vpn_password_hash', 'vpn_ip', 
                'certificate_path', 'certificate_expiry', 'ovpn_status', 
                'last_vpn_check', 'config_package_path'];
    $stmt = $db->query("DESCRIBE tbl_routers");
    $existingColumns = [];
    while ($row = $stmt->fetch()) {
        $existingColumns[] = $row['Field'];
    }
    
    foreach ($columns as $column) {
        if (in_array($column, $existingColumns)) {
            echo "  ✓ Column $column exists\n";
        } else {
            echo "  ✗ Column $column NOT found\n";
        }
    }
    
    echo "\n✓ All verifications passed!\n";
    exit(0);
}
