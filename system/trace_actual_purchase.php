<?php

/**
 * Trace Actual Purchase - Log the exact STK Push request being sent
 * 
 * This script patches the mpesa_create_transaction function to log the exact request
 */

// Include the application initialization
require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Trace Actual M-Pesa Purchase Request                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "This script will help identify the exact request being sent to M-Pesa.\n\n";

// Check if we have a recent failed transaction
$failed_trx = ORM::for_table('tbl_payment_gateway')
    ->where('payment_gateway', 'mpesa')
    ->where_raw('status IN (1, 4)') // Pending or Failed
    ->order_by_desc('id')
    ->find_one();

if (!$failed_trx) {
    echo "❌ No pending or failed M-Pesa transactions found.\n";
    echo "Please try to purchase a package first, then run this script.\n";
    exit(1);
}

echo "Found transaction:\n";
echo "----------------------------------------\n";
echo "Transaction ID: {$failed_trx['id']}\n";
echo "Username: {$failed_trx['username']}\n";
echo "Plan: {$failed_trx['plan_name']}\n";
echo "Amount: KES {$failed_trx['price']}\n";
echo "Status: {$failed_trx['status']}\n";
echo "Created: {$failed_trx['created_date']}\n";

if ($failed_trx['pg_request']) {
    echo "\nLast Request Sent:\n";
    echo "----------------------------------------\n";
    $request = json_decode($failed_trx['pg_request'], true);
    if ($request) {
        echo json_encode($request, JSON_PRETTY_PRINT) . "\n";
        
        // Analyze the request
        echo "\nRequest Analysis:\n";
        echo "----------------------------------------\n";
        
        if (isset($request['BusinessShortCode'])) {
            $shortcode = $request['BusinessShortCode'];
            echo "BusinessShortCode: '{$shortcode}'\n";
            echo "  - Type: " . gettype($shortcode) . "\n";
            echo "  - Length: " . strlen($shortcode) . "\n";
            echo "  - Is numeric: " . (is_numeric($shortcode) ? 'YES' : 'NO') . "\n";
            echo "  - Is string: " . (is_string($shortcode) ? 'YES' : 'NO') . "\n";
            echo "  - Is integer: " . (is_int($shortcode) ? 'YES' : 'NO') . "\n";
            echo "  - Hex dump: " . bin2hex($shortcode) . "\n";
            
            // Check if it's being sent as integer vs string
            if (is_int($shortcode)) {
                echo "\n⚠️  WARNING: BusinessShortCode is being sent as INTEGER!\n";
                echo "   M-Pesa API expects it as a STRING.\n";
                echo "   This could cause the 'invalid short code' error.\n";
            }
        }
        
        if (isset($request['PartyB'])) {
            $partyB = $request['PartyB'];
            echo "\nPartyB: '{$partyB}'\n";
            echo "  - Type: " . gettype($partyB) . "\n";
            
            if (is_int($partyB)) {
                echo "\n⚠️  WARNING: PartyB is being sent as INTEGER!\n";
                echo "   M-Pesa API expects it as a STRING.\n";
            }
        }
        
        if (isset($request['Amount'])) {
            $amount = $request['Amount'];
            echo "\nAmount: '{$amount}'\n";
            echo "  - Type: " . gettype($amount) . "\n";
            
            if (!is_int($amount) && !is_numeric($amount)) {
                echo "\n⚠️  WARNING: Amount should be numeric!\n";
            }
        }
    } else {
        echo "Failed to parse request JSON\n";
        echo "Raw: {$failed_trx['pg_request']}\n";
    }
}

if ($failed_trx['pg_paid_response']) {
    echo "\nLast Response Received:\n";
    echo "----------------------------------------\n";
    $response = json_decode($failed_trx['pg_paid_response'], true);
    if ($response) {
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
        
        // Check for error details
        if (isset($response['errorCode'])) {
            echo "\n❌ ERROR DETAILS:\n";
            echo "Error Code: {$response['errorCode']}\n";
            echo "Error Message: {$response['errorMessage']}\n";
        }
    } else {
        echo "Failed to parse response JSON\n";
        echo "Raw: {$failed_trx['pg_paid_response']}\n";
    }
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              RECOMMENDATIONS                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

echo "\n1. Check if shortcode is being sent as STRING (not integer)\n";
echo "2. Verify the shortcode matches your Daraja app exactly\n";
echo "3. Ensure you're using the correct environment (sandbox vs production)\n";
echo "4. Check if the callback URL is publicly accessible\n";

echo "\n=== Trace Complete ===\n";
