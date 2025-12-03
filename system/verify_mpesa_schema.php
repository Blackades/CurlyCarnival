<?php

/**
 * M-Pesa Database Schema Verification Script
 * 
 * This script checks the tbl_appconfig table structure to ensure
 * it can handle long M-Pesa credentials without truncation.
 */

// Load PHPNuxBill system
require_once '../init.php';

// Check if user is admin (for web access)
if (php_sapi_name() !== 'cli') {
    Admin::_auth();
}

echo "=== M-Pesa Database Schema Verification ===\n\n";

// Get database connection details
$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_password'];
$db_name = $config['db_name'];

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Step 1: Checking tbl_appconfig table structure...\n";
    
    // Get table structure
    $stmt = $pdo->query("DESCRIBE tbl_appconfig");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent table structure:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-15s %-30s %-10s %-10s\n", "Field", "Type", "Null", "Key");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($columns as $column) {
        printf("%-15s %-30s %-10s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Key']
        );
    }
    echo str_repeat("-", 80) . "\n\n";
    
    // Check if value column is appropriate
    $value_column = null;
    foreach ($columns as $column) {
        if ($column['Field'] === 'value') {
            $value_column = $column;
            break;
        }
    }
    
    if (!$value_column) {
        echo "ERROR: 'value' column not found in tbl_appconfig!\n";
        exit(1);
    }
    
    echo "Step 2: Analyzing 'value' column...\n";
    echo "  - Type: {$value_column['Type']}\n";
    echo "  - Null: {$value_column['Null']}\n";
    
    // Check if it's mediumtext or text
    $column_type = strtolower($value_column['Type']);
    
    if (strpos($column_type, 'mediumtext') !== false) {
        echo "  - Status: ✓ GOOD - MEDIUMTEXT can store up to 16MB\n";
        $needs_fix = false;
    } elseif (strpos($column_type, 'text') !== false && strpos($column_type, 'medium') === false) {
        echo "  - Status: ⚠ WARNING - TEXT can only store up to 64KB\n";
        echo "  - Recommendation: Upgrade to MEDIUMTEXT for better capacity\n";
        $needs_fix = true;
    } elseif (strpos($column_type, 'varchar') !== false) {
        preg_match('/varchar\((\d+)\)/', $column_type, $matches);
        $length = isset($matches[1]) ? $matches[1] : 0;
        echo "  - Status: ⚠ WARNING - VARCHAR($length) may truncate long values\n";
        echo "  - Recommendation: Upgrade to MEDIUMTEXT\n";
        $needs_fix = true;
    } else {
        echo "  - Status: ⚠ UNKNOWN - Unexpected column type\n";
        $needs_fix = true;
    }
    
    echo "\n";
    
    // Step 3: Check current M-Pesa values
    echo "Step 3: Checking current M-Pesa configuration values...\n";
    
    $mpesa_settings = [
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'mpesa_shortcode',
        'mpesa_passkey',
        'mpesa_environment'
    ];
    
    $stmt = $pdo->prepare("SELECT setting, LENGTH(value) as value_length, value FROM tbl_appconfig WHERE setting = ?");
    
    echo "\nCurrent M-Pesa settings:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-25s %-15s %-40s\n", "Setting", "Length", "Value (masked)");
    echo str_repeat("-", 80) . "\n";
    
    $has_values = false;
    foreach ($mpesa_settings as $setting) {
        $stmt->execute([$setting]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $has_values = true;
            $value = $row['value'];
            $length = $row['value_length'];
            
            // Mask the value for security
            if ($length > 8) {
                $masked = substr($value, 0, 4) . str_repeat('*', min($length - 8, 20)) . substr($value, -4);
            } else {
                $masked = str_repeat('*', $length);
            }
            
            printf("%-25s %-15s %-40s\n", $setting, $length . " chars", $masked);
            
            // Check for suspicious lengths
            if ($setting === 'mpesa_consumer_key' && $length < 20) {
                echo "  ⚠ WARNING: Consumer key seems too short (expected ~40-50 chars)\n";
            }
            if ($setting === 'mpesa_consumer_secret' && $length < 20) {
                echo "  ⚠ WARNING: Consumer secret seems too short (expected ~40-50 chars)\n";
            }
            if ($setting === 'mpesa_passkey' && $length < 30) {
                echo "  ⚠ WARNING: Passkey seems too short (expected ~40-50 chars)\n";
            }
            if ($setting === 'mpesa_shortcode' && ($length < 5 || $length > 10)) {
                echo "  ⚠ WARNING: Shortcode length unusual (expected 6-7 digits)\n";
            }
        } else {
            printf("%-25s %-15s %-40s\n", $setting, "0 chars", "(not set)");
        }
    }
    echo str_repeat("-", 80) . "\n\n";
    
    if (!$has_values) {
        echo "No M-Pesa configuration found. This is normal for new installations.\n\n";
    }
    
    // Step 4: Recommendations
    echo "Step 4: Recommendations\n";
    echo str_repeat("=", 80) . "\n";
    
    if ($needs_fix) {
        echo "⚠ ACTION REQUIRED: Database schema needs optimization\n\n";
        echo "Run the schema fix script to upgrade the column:\n";
        echo "  php fix_mpesa_schema.php\n\n";
    } else {
        echo "✓ Database schema is optimal for M-Pesa credentials\n\n";
    }
    
    if ($has_values) {
        echo "If you're experiencing issues:\n";
        echo "1. Run: php fix_mpesa_config.php (to clean and reset config)\n";
        echo "2. Re-enter credentials in admin panel\n";
        echo "3. Ensure no extra spaces or special characters\n";
        echo "4. Copy credentials directly from Daraja portal\n\n";
    }
    
    // Expected credential lengths
    echo "Expected M-Pesa credential lengths:\n";
    echo "  - Consumer Key:    40-50 characters (alphanumeric)\n";
    echo "  - Consumer Secret: 40-50 characters (alphanumeric)\n";
    echo "  - Shortcode:       6-7 digits (numeric only)\n";
    echo "  - Passkey:         40-50 characters (alphanumeric)\n";
    echo "  - Environment:     7-10 characters (sandbox/production)\n\n";
    
    echo "=== Verification Complete ===\n";
    
} catch (PDOException $e) {
    echo "ERROR: Database connection failed\n";
    echo "Message: " . $e->getMessage() . "\n";
    exit(1);
}
