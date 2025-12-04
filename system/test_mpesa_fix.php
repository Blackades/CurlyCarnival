<?php
/**
 * Test script to verify M-Pesa integration fix
 * This simulates what happens when a customer tries to purchase
 */

require_once('../init.php');

echo "=== Testing M-Pesa Integration Fix ===\n\n";

// Simulate finding a user
$user = ORM::for_table('tbl_customers')->where('username', '0706882144')->find_one();

if (!$user) {
    echo "ERROR: Test user not found\n";
    exit;
}

echo "1. Test user found: {$user['username']}\n";
echo "   Phone: {$user['phonenumber']}\n\n";

// Simulate finding the most recent M-Pesa transaction
$trx = ORM::for_table('tbl_payment_gateway')
    ->where('gateway', 'mpesa')
    ->where('username', $user['username'])
    ->order_by_desc('id')
    ->find_one();

if (!$trx) {
    echo "ERROR: No M-Pesa transaction found for testing\n";
    exit;
}

echo "2. Test transaction found:\n";
echo "   ID: {$trx['id']}\n";
echo "   Plan: {$trx['plan_name']}\n";
echo "   Price: {$trx['price']}\n";
echo "   Status: {$trx['status']}\n\n";

// Load the M-Pesa gateway file
$gateway = 'mpesa';
include_once $PAYMENTGATEWAY_PATH . DIRECTORY_SEPARATOR . $gateway . '.php';

echo "3. M-Pesa gateway file loaded\n\n";

// Check if function exists
if (!function_exists('mpesa_create_transaction')) {
    echo "ERROR: mpesa_create_transaction function not found\n";
    exit;
}

echo "4. mpesa_create_transaction function exists\n\n";

// Test if we can call the function (dry run - we'll catch the redirect)
echo "5. Testing function call...\n";
echo "   This will attempt to initiate STK Push\n";
echo "   You should see either:\n";
echo "   - A redirect message (if successful)\n";
echo "   - An error message (if there's an issue)\n\n";

try {
    // Convert ORM object to array for the function
    $user_array = $user->as_array();
    
    // Call the function - this will redirect, so we won't see output after this
    mpesa_create_transaction($trx, $user_array);
    
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
