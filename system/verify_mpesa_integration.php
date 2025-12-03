<?php
/**
 * M-Pesa Integration Verification Script
 * This script verifies that M-Pesa is properly integrated into the payment gateway system
 */

// Include the system initialization
require_once dirname(__DIR__) . '/init.php';

echo "=== M-Pesa Payment Gateway Integration Verification ===\n\n";

// 1. Check if M-Pesa gateway file exists
echo "1. Checking M-Pesa Gateway File...\n";
$mpesa_file = $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . 'mpesa.php';
if (file_exists($mpesa_file)) {
    echo "   ✓ M-Pesa gateway file exists at: $mpesa_file\n";
} else {
    echo "   ✗ M-Pesa gateway file NOT found at: $mpesa_file\n";
    exit(1);
}

// 2. Check if M-Pesa functions are defined
echo "\n2. Checking M-Pesa Functions...\n";
include_once $mpesa_file;

$required_functions = [
    'mpesa_validate_config',
    'mpesa_show_config',
    'mpesa_save_config',
    'mpesa_create_transaction',
    'mpesa_payment_notification',
    'mpesa_get_status'
];

$all_functions_exist = true;
foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "   ✓ Function $func() exists\n";
    } else {
        echo "   ✗ Function $func() NOT found\n";
        $all_functions_exist = false;
    }
}

if (!$all_functions_exist) {
    echo "\n   ERROR: Some required functions are missing!\n";
    exit(1);
}

// 3. Check if M-Pesa appears in gateway list
echo "\n3. Checking Payment Gateway Discovery...\n";
$files = scandir($PAYMENTGATEWAY_PATH);
$pgs = [];
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
        $pgs[] = str_replace('.php', '', $file);
    }
}

if (in_array('mpesa', $pgs)) {
    echo "   ✓ M-Pesa is discoverable in payment gateway list\n";
    echo "   Available gateways: " . implode(', ', $pgs) . "\n";
} else {
    echo "   ✗ M-Pesa NOT found in gateway list\n";
    echo "   Available gateways: " . implode(', ', $pgs) . "\n";
    exit(1);
}

// 4. Check M-Pesa configuration in database
echo "\n4. Checking M-Pesa Configuration...\n";
$config_keys = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

$configured = true;
foreach ($config_keys as $key) {
    $value = ORM::for_table('tbl_appconfig')->where('setting', $key)->find_one();
    if ($value && !empty($value['value'])) {
        echo "   ✓ $key is configured\n";
    } else {
        echo "   ⚠ $key is NOT configured (this is normal for new installations)\n";
        $configured = false;
    }
}

// 5. Check if M-Pesa is in active payment gateways
echo "\n5. Checking Active Payment Gateways...\n";
$active_gateways = ORM::for_table('tbl_appconfig')->where('setting', 'payment_gateway')->find_one();
if ($active_gateways) {
    $actives = explode(',', $active_gateways['value']);
    if (in_array('mpesa', $actives)) {
        echo "   ✓ M-Pesa is ENABLED in active payment gateways\n";
        echo "   Active gateways: " . $active_gateways['value'] . "\n";
    } else {
        echo "   ⚠ M-Pesa is NOT enabled in active payment gateways\n";
        echo "   Active gateways: " . $active_gateways['value'] . "\n";
        echo "   To enable: Go to /paymentgateway and check the M-Pesa checkbox\n";
    }
} else {
    echo "   ⚠ No active payment gateways configured\n";
    echo "   To enable: Go to /paymentgateway and check the M-Pesa checkbox\n";
}

// 6. Check M-Pesa template file
echo "\n6. Checking M-Pesa Template File...\n";
$template_file = $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'mpesa.tpl';
if (file_exists($template_file)) {
    echo "   ✓ M-Pesa template file exists at: $template_file\n";
} else {
    echo "   ✗ M-Pesa template file NOT found at: $template_file\n";
}

// 7. Check callback endpoint
echo "\n7. Checking Callback Configuration...\n";
$callback_url = U . 'callback/mpesa';
echo "   ℹ Callback URL: $callback_url\n";
echo "   ℹ This URL must be registered with Safaricom Daraja API\n";

// Summary
echo "\n=== Verification Summary ===\n";
echo "✓ M-Pesa gateway file exists and all required functions are defined\n";
echo "✓ M-Pesa is discoverable by the payment gateway system\n";
if ($configured) {
    echo "✓ M-Pesa credentials are configured\n";
} else {
    echo "⚠ M-Pesa credentials need to be configured (visit /paymentgateway/mpesa)\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Visit " . U . "paymentgateway to enable M-Pesa\n";
echo "2. Visit " . U . "paymentgateway/mpesa to configure credentials\n";
echo "3. Register callback URL with Safaricom: $callback_url\n";
echo "4. Test payment flow: Select a package → Choose M-Pesa → Complete payment\n";

echo "\n=== Integration Status: SUCCESS ===\n";
echo "M-Pesa is properly integrated and ready to be configured!\n";
