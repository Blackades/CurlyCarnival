<?php

/**
 * Fix M-Pesa Configuration Script
 * 
 * This script drops and recreates M-Pesa configuration entries in tbl_appconfig
 * to fix corrupted values that cause "400.002.02 invalid shortcode" errors.
 * 
 * Usage:
 * 1. Run this script from command line: php fix_mpesa_config.php
 * 2. Or access via browser: http://yoursite.com/system/fix_mpesa_config.php
 * 3. After running, reconfigure M-Pesa settings in admin panel
 */

// Load PHPNuxBill system
require_once '../init.php';

// Check if user is admin (for web access)
if (php_sapi_name() !== 'cli') {
    Admin::_auth();
}

echo "=== M-Pesa Configuration Fix Script ===\n\n";

// Step 1: Backup existing M-Pesa configuration
echo "Step 1: Backing up existing M-Pesa configuration...\n";

$mpesa_settings = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

$backup = [];
foreach ($mpesa_settings as $setting) {
    $config_entry = ORM::for_table('tbl_appconfig')
        ->where('setting', $setting)
        ->find_one();
    
    if ($config_entry) {
        $backup[$setting] = $config_entry->value;
        echo "  - Backed up: $setting = " . substr($config_entry->value, 0, 20) . "...\n";
    } else {
        echo "  - Not found: $setting\n";
    }
}

echo "\nBackup completed. Found " . count($backup) . " settings.\n\n";

// Step 2: Delete existing M-Pesa configuration entries
echo "Step 2: Deleting existing M-Pesa configuration entries...\n";

foreach ($mpesa_settings as $setting) {
    $deleted = ORM::for_table('tbl_appconfig')
        ->where('setting', $setting)
        ->delete_many();
    
    if ($deleted > 0) {
        echo "  - Deleted: $setting\n";
    }
}

echo "\nDeletion completed.\n\n";

// Step 3: Verify deletion
echo "Step 3: Verifying deletion...\n";

$remaining = 0;
foreach ($mpesa_settings as $setting) {
    $config_entry = ORM::for_table('tbl_appconfig')
        ->where('setting', $setting)
        ->find_one();
    
    if ($config_entry) {
        echo "  - WARNING: $setting still exists!\n";
        $remaining++;
    }
}

if ($remaining === 0) {
    echo "  - All M-Pesa settings successfully deleted.\n";
} else {
    echo "  - WARNING: $remaining settings still exist. Manual cleanup may be required.\n";
}

echo "\n=== Fix Completed ===\n\n";

echo "Next Steps:\n";
echo "1. Go to Admin Panel > Settings > Payment Gateway > M-Pesa\n";
echo "2. Re-enter your M-Pesa credentials:\n";
echo "   - Consumer Key\n";
echo "   - Consumer Secret\n";
echo "   - Business Shortcode\n";
echo "   - Passkey\n";
echo "   - Environment (Sandbox or Production)\n";
echo "3. Save the configuration\n";
echo "4. Test with a small transaction\n\n";

if (!empty($backup)) {
    echo "Backed up values (for reference):\n";
    foreach ($backup as $key => $value) {
        // Mask sensitive data
        $masked_value = substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
        echo "  - $key: $masked_value\n";
    }
    echo "\n";
}

echo "IMPORTANT: Make sure to enter the correct values from your Safaricom Daraja API portal.\n";
echo "The shortcode should be numeric only (e.g., 174379 or 600XXX).\n\n";
