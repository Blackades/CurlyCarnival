<?php
/**
 * Manual fix script for transaction 47
 * Run this from command line: php fix_transaction_47.php
 */

// Include the application bootstrap
require_once 'system/autoload/ORM.php';
require_once 'system/autoload/DB.php';

// Database configuration - update these with your actual values
$db_host = 'localhost';
$db_name = 'phpnuxbill';
$db_user = 'root';
$db_pass = ''; // Add your password here

// Initialize ORM
ORM::configure("mysql:host=$db_host;dbname=$db_name");
ORM::configure('username', $db_user);
ORM::configure('password', $db_pass);

// Find transaction 47
$trx = ORM::for_table('tbl_payment_gateway')->find_one(47);

if (!$trx) {
    die("Transaction 47 not found\n");
}

echo "Current status: {$trx->status}\n";
echo "Current paid_date: {$trx->paid_date}\n";

// Update to paid status
$trx->status = 2; // PAID
$trx->paid_date = date('Y-m-d H:i:s');
$trx->payment_method = 'M-Pesa';
$trx->payment_channel = 'M-Pesa STK Push';
$trx->pg_paid_response = json_encode([
    'receipt_number' => 'TL5K502PJQ',
    'amount' => 10.00,
    'phone' => 254706882144,
    'transaction_date' => 20251205031021,
    'manual_fix' => true
]);

if ($trx->save()) {
    echo "Transaction updated successfully!\n";
    echo "New status: {$trx->status}\n";
    echo "New paid_date: {$trx->paid_date}\n";
    
    // Now process the user activation
    require_once 'system/paymentgateway/mpesastk.php';
    
    try {
        mpesastk_process_successful_payment($trx);
        echo "User activated successfully!\n";
    } catch (Exception $e) {
        echo "Error activating user: " . $e->getMessage() . "\n";
    }
} else {
    echo "Failed to update transaction\n";
}
