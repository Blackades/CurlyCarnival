<?php

/**
 * M-Pesa Database Schema Fix Script
 * 
 * This script ensures the tbl_appconfig table can handle long M-Pesa
 * credentials without truncation by upgrading the column type if needed.
 */

// Load PHPNuxBill system
require_once '../init.php';

// Check if user is admin (for web access)
if (php_sapi_name() !== 'cli') {
    Admin::_auth();
}

echo "=== M-Pesa Database Schema Fix ===\n\n";

// Get database connection details
$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_password'];
$db_name = $config['db_name'];

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Step 1: Checking current schema...\n";
    
    // Get current column definition
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_appconfig WHERE Field = 'value'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$column) {
        echo "ERROR: 'value' column not found!\n";
        exit(1);
    }
    
    $current_type = $column['Type'];
    echo "  - Current type: $current_type\n";
    
    // Check if we need to modify
    $needs_modification = false;
    
    if (stripos($current_type, 'mediumtext') !== false) {
        echo "  - Status: Already using MEDIUMTEXT (optimal)\n";
    } elseif (stripos($current_type, 'text') !== false) {
        echo "  - Status: Using TEXT (will upgrade to MEDIUMTEXT)\n";
        $needs_modification = true;
    } elseif (stripos($current_type, 'varchar') !== false) {
        echo "  - Status: Using VARCHAR (will upgrade to MEDIUMTEXT)\n";
        $needs_modification = true;
    } else {
        echo "  - Status: Unknown type (will upgrade to MEDIUMTEXT)\n";
        $needs_modification = true;
    }
    
    echo "\n";
    
    if (!$needs_modification) {
        echo "✓ No schema modification needed. Column is already optimal.\n\n";
        echo "If you're still experiencing issues, the problem may be with:\n";
        echo "1. The actual credential values (extra spaces, wrong format)\n";
        echo "2. Character encoding issues\n";
        echo "3. Application-level validation\n\n";
        echo "Run: php verify_mpesa_schema.php to check current values\n";
        echo "Run: php fix_mpesa_config.php to reset configuration\n";
        exit(0);
    }
    
    // Backup current data
    echo "Step 2: Backing up current M-Pesa configuration...\n";
    
    $mpesa_settings = [
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'mpesa_shortcode',
        'mpesa_passkey',
        'mpesa_environment'
    ];
    
    $backup = [];
    $stmt = $pdo->prepare("SELECT setting, value FROM tbl_appconfig WHERE setting = ?");
    
    foreach ($mpesa_settings as $setting) {
        $stmt->execute([$setting]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $backup[$setting] = $row['value'];
            echo "  - Backed up: $setting (" . strlen($row['value']) . " chars)\n";
        }
    }
    
    echo "  - Backup complete: " . count($backup) . " settings saved\n\n";
    
    // Modify the column
    echo "Step 3: Modifying column structure...\n";
    echo "  - Changing 'value' column to MEDIUMTEXT...\n";
    
    $sql = "ALTER TABLE tbl_appconfig 
            MODIFY COLUMN `value` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL";
    
    $pdo->exec($sql);
    
    echo "  - ✓ Column modified successfully\n\n";
    
    // Verify the change
    echo "Step 4: Verifying modification...\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_appconfig WHERE Field = 'value'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $new_type = $column['Type'];
    echo "  - New type: $new_type\n";
    
    if (stripos($new_type, 'mediumtext') !== false) {
        echo "  - ✓ Verification successful\n\n";
    } else {
        echo "  - ⚠ WARNING: Column type may not have changed as expected\n\n";
    }
    
    // Restore backed up data
    if (!empty($backup)) {
        echo "Step 5: Restoring M-Pesa configuration...\n";
        
        $stmt = $pdo->prepare("UPDATE tbl_appconfig SET value = ? WHERE setting = ?");
        
        foreach ($backup as $setting => $value) {
            $stmt->execute([$value, $setting]);
            echo "  - Restored: $setting\n";
        }
        
        echo "  - ✓ All settings restored\n\n";
    }
    
    echo "=== Schema Fix Complete ===\n\n";
    
    echo "Summary:\n";
    echo "  - Column type upgraded to MEDIUMTEXT\n";
    echo "  - Maximum storage capacity: 16MB per value\n";
    echo "  - Character encoding: utf8mb4\n";
    echo "  - Collation: utf8mb4_general_ci\n\n";
    
    if (!empty($backup)) {
        echo "Your M-Pesa configuration has been preserved.\n";
        echo "If you're still experiencing issues:\n";
        echo "1. Run: php verify_mpesa_schema.php (to check values)\n";
        echo "2. Run: php fix_mpesa_config.php (to reset and re-enter)\n\n";
    } else {
        echo "No M-Pesa configuration found.\n";
        echo "Configure M-Pesa in: Admin Panel > Payment Gateway > M-Pesa\n\n";
    }
    
    echo "The database is now ready to handle long M-Pesa credentials.\n";
    
} catch (PDOException $e) {
    echo "ERROR: Database operation failed\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "\nIf you see a permissions error, run this SQL manually:\n\n";
    echo "ALTER TABLE tbl_appconfig \n";
    echo "MODIFY COLUMN `value` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;\n\n";
    exit(1);
}
