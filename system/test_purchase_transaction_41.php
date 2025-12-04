<?php
/**
 * Test script to manually trigger mpesa_create_transaction for transaction 41
 */

require_once('../init.php');

echo "=== Testing M-Pesa Transaction 41 ===\n\n";

// Get transaction 41
$trx = ORM::for_table('tbl_payment_gateway')->find_one(41);

if (!$trx) {
    die("Transaction 41 not found\n");
}

echo "Transaction found:\n";
echo "  ID: {$trx['id']}\n";
echo "  Username: {$trx['username']}\n";
echo "  Gateway: {$trx['gateway']}\n";
echo "  Plan: {$trx['plan_name']}\n";
echo "  Price: {$trx['price']}\n";
echo "  Status: {$trx['status']}\n\n";

// Get user
$user = ORM::for_table('tbl_customers')->where('username', $trx['username'])->find_one();

if (!$user) {
    die("User not found\n");
}

echo "User found:\n";
echo "  Username: {$user['username']}\n";
echo "  Phone: {$user['phonenumber']}\n\n";

// Load M-Pesa gateway
$gateway_file = $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . 'mpesa.php';
echo "Loading gateway file: $gateway_file\n";

if (!file_exists($gateway_file)) {
    die("Gateway file not found\n");
}

include_once $gateway_file;

echo "Gateway file loaded\n\n";

// Check if function exists
if (!function_exists('mpesa_create_transaction')) {
    die("mpesa_create_transaction function not found\n");
}

echo "Function exists: mpesa_create_transaction\n\n";

// Call the function
echo "Calling mpesa_create_transaction...\n\n";

try {
    mpesa_create_transaction($trx, $user);
    echo "\n✅ Function executed successfully\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Check transaction again
$trx_after = ORM::for_table('tbl_payment_gateway')->find_one(41);

echo "\nTransaction after function call:\n";
echo "  CheckoutRequestID: " . ($trx_after['gateway_trx_id'] ? $trx_after['gateway_trx_id'] : 'EMPTY') . "\n";
echo "  Request Data: " . ($trx_after['pg_request'] ? 'PRESENT' : 'EMPTY') . "\n";
echo "  pg_url_payment: " . ($trx_after['pg_url_payment'] ? $trx_after['pg_url_payment'] : 'EMPTY') . "\n";

echo "\n=== Test Complete ===\n";
