<?php
/**
 * Fix VPN Audit Log Table Creation
 * 
 * This script creates the tbl_vpn_audit_log table without the problematic foreign key constraint
 */

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

echo "Fixing tbl_vpn_audit_log table...\n";
echo str_repeat("=", 60) . "\n\n";

// Drop table if exists
try {
    $db->exec("DROP TABLE IF EXISTS `tbl_vpn_audit_log`");
    echo "✓ Dropped existing table (if any)\n";
} catch (PDOException $e) {
    echo "⊘ No existing table to drop\n";
}

// Create table without foreign key constraint on admin_id
$createTableSQL = "CREATE TABLE `tbl_vpn_audit_log` (
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
    INDEX `idx_router_id` (`router_id`),
    INDEX `idx_admin_id` (`admin_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $db->exec($createTableSQL);
    echo "✓ Created tbl_vpn_audit_log table successfully\n\n";
    
    // Verify table exists
    $stmt = $db->query("SHOW TABLES LIKE 'tbl_vpn_audit_log'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Verification: Table exists\n";
        
        // Show table structure
        echo "\nTable structure:\n";
        $stmt = $db->query("DESCRIBE tbl_vpn_audit_log");
        while ($row = $stmt->fetch()) {
            echo "  - {$row['Field']} ({$row['Type']})\n";
        }
        
        echo "\n✓ All done! The tbl_vpn_audit_log table is now ready.\n";
        exit(0);
    } else {
        echo "✗ Verification failed: Table not found\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "✗ Failed to create table: " . $e->getMessage() . "\n";
    exit(1);
}
