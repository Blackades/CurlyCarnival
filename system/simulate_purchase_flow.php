<?php
/**
 * Simulate Actual Purchase Flow
 * 
 * This script simulates exactly what happens when a user clicks "Buy" on a package
 */

echo "=== Simulating Actual Purchase Flow ===\n\n";

// Load application
$config_path = __DIR__ . '/../config.php';
require_once($config_path);
require_once(__DIR__ . '/../init.php');

global $config;

echo "Step 1: Loading M-Pesa gateway...\n";
$gateway = 'mpesa';
$PAYMENTGATEWAY_PATH = __DIR__ . '/paymentgateway';
include $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $gateway . '.php';

echo "✓ M-Pesa gateway loaded\n\n";

echo "Step 2: Validating config...\n";
try {
    call_user_func($gateway . '_validate_config');
    echo "✓ Config validation passed\n\n";
} catch (Exception $e) {
    die("✗ Config validation failed: " . $e->getMessage() . "\n");
}

echo "Step 3: Creating test transaction record...\n";

// Find a test user
$user = ORM::for_table('tbl_customers')->where('status', 'Active')->find_one();
if (!$user) {
    die("✗ No active customer found in database\n");
}

echo "Using customer: " . $user['username'] . " (ID: " . $user['id'] . ")\n";
echo "Phone: " . $user['phonenumber'] . "\n\n";

// Find a test plan
$plan = ORM::for_table('tbl_plans')->where('enabled', '1')->where('type', 'Hotspot')->find_one();
if (!$plan) {
    die("✗ No enabled plan found\n");
}

echo "Using plan: " . $plan['name_plan'] . " (Price: " . $plan['price'] . ")\n\n";

// Create transaction record (exactly like the controller does)
echo "Step 4: Creating transaction record in database...\n";

$d = ORM::for_table('tbl_payment_gateway')->create();
$d->username = $user['username'];
$d->user_id = $user['id'];
$d->gateway = $gateway;
$d->plan_id = $plan['id'];
$d->plan_name = $plan['name_plan'];
$d->routers_id = 0;
$d->routers = 'radius';
$d->price = $plan['price'];
$d->created_date = date('Y-m-d H:i:s');
$d->status = 1;
$d->save();

$trx_id = $d->id();
echo "✓ Transaction created with ID: $trx_id\n\n";

// Reload the transaction (to simulate what happens in real flow)
$d = ORM::for_table('tbl_payment_gateway')->find_one($trx_id);

echo "Step 5: Preparing to call mpesa_create_transaction...\n";
echo "Transaction data:\n";
echo "  ID: " . $d['id'] . "\n";
echo "  Username: " . $d['username'] . "\n";
echo "  Plan: " . $d['plan_name'] . "\n";
echo "  Price: " . $d['price'] . "\n";
echo "  Gateway: " . $d['gateway'] . "\n\n";

echo "User data:\n";
echo "  Username: " . $user['username'] . "\n";
echo "  Phone: " . $user['phonenumber'] . "\n";
echo "  Email: " . $user['email'] . "\n\n";

echo "Config data:\n";
echo "  Shortcode: " . $config['mpesa_shortcode'] . "\n";
echo "  Environment: " . $config['mpesa_environment'] . "\n";
echo "  Consumer Key: " . substr($config['mpesa_consumer_key'], 0, 10) . "...\n\n";

echo "Step 6: Calling mpesa_create_transaction (THIS IS WHERE THE ERROR OCCURS)...\n";
echo "========================================================================\n\n";

try {
    // This is the EXACT call that happens in order.php line 702
    call_user_func($gateway . '_create_transaction', $d, $user);
    
    echo "\n========================================================================\n";
    echo "✓ Function completed without fatal errors\n";
    echo "Check if you were redirected or if there were any errors above.\n";
    
} catch (Exception $e) {
    echo "\n========================================================================\n";
    echo "✗ EXCEPTION CAUGHT: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n\nStep 7: Checking transaction status...\n";
$d = ORM::for_table('tbl_payment_gateway')->find_one($trx_id);
echo "Transaction status: " . $d['status'] . "\n";
echo "Gateway TRX ID: " . ($d['gateway_trx_id'] ?: 'Not set') . "\n";
echo "PG Request: " . ($d['pg_request'] ?: 'Not set') . "\n";

echo "\n=== Simulation Complete ===\n";
echo "If you saw a 400.002.02 error above, that's the issue we need to fix.\n";
