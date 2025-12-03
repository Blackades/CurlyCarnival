<?php

/**
 * Test Fixed STK Push - Verify the shortcode fix works
 * 
 * This script tests the actual STK Push with the fixed shortcode handling
 */

require_once '../init.php';

// Include the mpesa payment gateway functions
require_once 'paymentgateway/mpesa.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Test Fixed M-Pesa STK Push                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Load M-Pesa configuration
echo "Step 1: Loading M-Pesa configuration...\n";
echo "----------------------------------------\n";

$consumer_key = $config['mpesa_consumer_key'];
$consumer_secret = $config['mpesa_consumer_secret'];
$shortcode = (string)$config['mpesa_shortcode']; // FIXED: Force string type
$passkey = $config['mpesa_passkey'];
$environment = $config['mpesa_environment'];

echo "✓ Consumer Key: " . substr($consumer_key, 0, 4) . "..." . substr($consumer_key, -4) . "\n";
echo "✓ Consumer Secret: " . substr($consumer_secret, 0, 4) . "..." . substr($consumer_secret, -4) . "\n";
echo "✓ Shortcode: {$shortcode} (Type: " . gettype($shortcode) . ")\n";
echo "✓ Passkey: " . substr($passkey, 0, 4) . "..." . substr($passkey, -4) . "\n";
echo "✓ Environment: {$environment}\n\n";

// Step 2: Get OAuth token
echo "Step 2: Getting OAuth access token...\n";
echo "----------------------------------------\n";

try {
    $access_token = mpesa_get_access_token();
    echo "✓ Access token obtained: " . substr($access_token, 0, 20) . "...\n\n";
} catch (Exception $e) {
    echo "❌ Failed to get access token: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 3: Prepare STK Push request
echo "Step 3: Preparing STK Push request...\n";
echo "----------------------------------------\n";

$phone = readline("Enter test phone number (format: 254XXXXXXXXX): ");
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

$request_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => 1,
    'PartyA' => $phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $phone,
    'CallBackURL' => U . 'callback/mpesa',
    'AccountReference' => 'TEST',
    'TransactionDesc' => 'Test Payment'
];

echo "\nRequest Data Types:\n";
echo "  BusinessShortCode: " . gettype($request_data['BusinessShortCode']) . " = '{$request_data['BusinessShortCode']}'\n";
echo "  PartyB: " . gettype($request_data['PartyB']) . " = '{$request_data['PartyB']}'\n";
echo "  Amount: " . gettype($request_data['Amount']) . " = {$request_data['Amount']}\n";

echo "\nJSON Encoding Test:\n";
$json = json_encode($request_data, JSON_PRETTY_PRINT);
echo $json . "\n";

// Check if shortcode is encoded as string
if (strpos($json, '"BusinessShortCode": "' . $shortcode . '"') !== false) {
    echo "\n✓ BusinessShortCode is correctly encoded as STRING\n";
} else if (strpos($json, '"BusinessShortCode": ' . $shortcode) !== false) {
    echo "\n❌ WARNING: BusinessShortCode is encoded as INTEGER!\n";
    echo "This will cause the 'invalid short code' error.\n";
    exit(1);
}

if (strpos($json, '"PartyB": "' . $shortcode . '"') !== false) {
    echo "✓ PartyB is correctly encoded as STRING\n\n";
} else if (strpos($json, '"PartyB": ' . $shortcode) !== false) {
    echo "\n❌ WARNING: PartyB is encoded as INTEGER!\n";
    echo "This will cause the 'invalid short code' error.\n";
    exit(1);
}

// Step 4: Send STK Push request
echo "Step 4: Sending STK Push request...\n";
echo "----------------------------------------\n";

$base_url = mpesa_get_base_url();
$stk_push_url = $base_url . '/mpesa/stkpush/v1/processrequest';

try {
    $response = Http::postJsonData($stk_push_url, $request_data, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    
    $result = json_decode($response, true);
    
    echo "Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                              SUCCESS!                                      ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
        echo "\n✓ STK Push initiated successfully!\n";
        echo "✓ CheckoutRequestID: {$result['CheckoutRequestID']}\n";
        echo "✓ The fix is working correctly!\n";
        echo "\nThe phone should receive an STK push prompt.\n";
    } else {
        echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                              FAILED                                        ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
        
        $error_code = isset($result['errorCode']) ? $result['errorCode'] : 'Unknown';
        $error_message = isset($result['errorMessage']) ? $result['errorMessage'] : 'Unknown error';
        
        echo "\n❌ STK Push failed\n";
        echo "Error Code: {$error_code}\n";
        echo "Error Message: {$error_message}\n";
        
        if ($error_code == '400.002.02') {
            echo "\n⚠️  Still getting 'invalid short code' error!\n";
            echo "This suggests the issue may be elsewhere.\n";
            echo "Please check:\n";
            echo "  1. Shortcode matches your Daraja app exactly\n";
            echo "  2. You're using the correct environment (sandbox vs production)\n";
            echo "  3. Your Daraja app is active and not suspended\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
