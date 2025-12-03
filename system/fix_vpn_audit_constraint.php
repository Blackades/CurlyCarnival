<?php
/**
 * Fix VPN Audit Log Foreign Key Constraint Issue
 * 
 * This script drops and recreates the tbl_vpn_audit_log table to fix constraint issues
 */

// Load configuration
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    die('Error: config.php not found.');
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

echo "Fixing tbl_vpn_audit_log foreign key constraint...\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Drop the table if it exists
    echo "[1/2] Dropping existing table...\n";
    $db->exec("DROP TABLE IF EXISTS `tbl_vpn_audit_log`");
    echo "✓ Table dropped\n\n";
    
    // Recreate the table without the problematic foreign key
    echo "[2/2] Creating table with correct structure...\n";
    $createSQL = "CREATE TABLE `tbl_vpn_audit_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `router_id` INT NOT NULL,
        `action` VARCHAR(50) NOT NULL,
        `admin_id` INT NOT NULL,
        `details` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
        `error_message` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_router_id` (`router_id`),
        INDEX `idx_admin_id` (`admin_id`),
        INDEX `idx_created_at` (`created_at`),
        CONSTRAINT `fk_vpn_audit_router` FOREIGN KEY (`router_id`) REFERENCES `tbl_routers`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $db->exec($createSQL);
    echo "✓ Table created successfully\n\n";
    
    // Verify
    $stmt = $db->query("SHOW TABLES LIKE 'tbl_vpn_audit_log'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Verification passed\n";
        echo "\n✓ Fix completed successfully!\n";
        exit(0);
    } else {
        echo "✗ Verification failed\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
