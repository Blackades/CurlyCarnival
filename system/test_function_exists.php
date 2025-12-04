<?php

/**
 * Test if M-Pesa functions exist
 */

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Test M-Pesa Functions                                        ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Load init
require_once '../init.php';

echo "Step 1: Loading mpesa.php...\n";
echo "----------------------------------------\n";

$mpesa_file = 'paymentgateway/mpesa.php';
if (file_exists($mpesa_file)) {
    echo "✓ File exists: {$mpesa_file}\n";
    require_once $mpesa_file;
    echo "✓ File loaded successfully\n\n";
} else {
    echo "❌ File NOT found: {$mpesa_file}\n";
    exit(1);
}

echo "Step 2: Checking if functions exist...\n";
echo "----------------------------------------\n";

$functions = [
    'mpesa_validate_config',
    'mpesa_show_config',
    'mpesa_save_config',
    'mpesa_create_transaction',
    'mpesa_payment_notification',
    'mpesa_get_status',
    'mpesa_get_access_token',
    'mpesa_format_phone_number',
    'mpesa_generate_password'
];

$all_exist = true;
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✓ {$func}() exists\n";
    } else {
        echo "❌ {$func}() NOT FOUND\n";
        $all_exist = false;
    }
}

echo "\n";
if ($all_exist) {
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                              SUCCESS!                                      ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    echo "✓ All M-Pesa functions are loaded and available\n";
    echo "✓ The payment gateway should work\n\n";
    echo "If purchases still fail, the issue is in how the system calls these functions.\n";
} else {
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                              FAILED                                        ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    echo "❌ Some functions are missing\n";
    echo "This means there's a syntax error or issue in mpesa.php\n";
}

echo "\n=== Test Complete ===\n";
