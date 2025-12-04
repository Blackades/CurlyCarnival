<?php
/**
 * Debug script to check if mpesa_create_transaction is being called
 * Run this after attempting a purchase to see what happened
 */

require_once('../init.php');

echo "=== M-Pesa Gateway Debug ===\n\n";

// Check if PAYMENTGATEWAY_PATH is defined
echo "1. PAYMENTGATEWAY_PATH: " . (isset($PAYMENTGATEWAY_PATH) ? $PAYMENTGATEWAY_PATH : 'NOT DEFINED') . "\n\n";

// Check if mpesa.php file exists
$mpesa_file = $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . 'mpesa.php';
echo "2. M-Pesa file path: $mpesa_file\n";
echo "3. M-Pesa file exists: " . (file_exists($mpesa_file) ? 'YES' : 'NO') . "\n\n";

// Try to include the file
if (file_exists($mpesa_file)) {
    include_once $mpesa_file;
    echo "4. M-Pesa file included successfully\n\n";
    
    // Check if functions exist
    $functions = [
        'mpesa_validate_config',
        'mpesa_show_config',
        'mpesa_save_config',
        'mpesa_create_transaction',
        'mpesa_payment_notification',
        'mpesa_get_status'
    ];
    
    echo "5. Function availability:\n";
    foreach ($functions as $func) {
        echo "   - $func: " . (function_exists($func) ? 'EXISTS' : 'NOT FOUND') . "\n";
    }
    echo "\n";
} else {
    echo "4. ERROR: M-Pesa file not found!\n\n";
}

// Check M-Pesa configuration
echo "6. M-Pesa Configuration:\n";
echo "   - Consumer Key: " . (empty($config['mpesa_consumer_key']) ? 'NOT SET' : 'SET (length: ' . strlen($config['mpesa_consumer_key']) . ')') . "\n";
echo "   - Consumer Secret: " . (empty($config['mpesa_consumer_secret']) ? 'NOT SET' : 'SET (length: ' . strlen($config['mpesa_consumer_secret']) . ')') . "\n";
echo "   - Shortcode: " . (empty($config['mpesa_shortcode']) ? 'NOT SET' : $config['mpesa_shortcode']) . "\n";
echo "   - Passkey: " . (empty($config['mpesa_passkey']) ? 'NOT SET' : 'SET (length: ' . strlen($config['mpesa_passkey']) . ')') . "\n";
echo "   - Environment: " . (empty($config['mpesa_environment']) ? 'NOT SET' : $config['mpesa_environment']) . "\n\n";

// Check recent M-Pesa transactions
echo "7. Recent M-Pesa transactions:\n";
$transactions = ORM::for_table('tbl_payment_gateway')
    ->where('gateway', 'mpesa')
    ->order_by_desc('id')
    ->limit(5)
    ->find_many();

if (count($transactions) > 0) {
    foreach ($transactions as $trx) {
        echo "   - ID: {$trx['id']}, Status: {$trx['status']}, Created: {$trx['created_date']}\n";
        echo "     Username: {$trx['username']}, Plan: {$trx['plan_name']}, Price: {$trx['price']}\n";
        echo "     CheckoutRequestID: " . ($trx['gateway_trx_id'] ? $trx['gateway_trx_id'] : 'EMPTY') . "\n";
        echo "     Request Data: " . ($trx['pg_request'] ? 'PRESENT' : 'EMPTY') . "\n";
        echo "     Response Data: " . ($trx['pg_paid_response'] ? 'PRESENT' : 'EMPTY') . "\n\n";
    }
} else {
    echo "   No M-Pesa transactions found\n\n";
}

// Check if M-Pesa is in enabled payment gateways
echo "8. Enabled Payment Gateways: " . (isset($config['payment_gateway']) ? $config['payment_gateway'] : 'NOT SET') . "\n";
if (isset($config['payment_gateway'])) {
    $gateways = explode(',', $config['payment_gateway']);
    echo "   M-Pesa enabled: " . (in_array('mpesa', $gateways) ? 'YES' : 'NO') . "\n";
}

echo "\n=== End Debug ===\n";
