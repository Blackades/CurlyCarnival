<?php
/**
 * Actual M-Pesa STK Push Test
 * 
 * This script performs a REAL STK Push request to Safaricom's API
 * to see the exact error response.
 */

echo "=== Actual M-Pesa STK Push Test ===\n\n";

// Load application
$config_path = __DIR__ . '/../config.php';
if (!file_exists($config_path)) {
    die("ERROR: config.php not found\n");
}

require_once($config_path);
require_once(__DIR__ . '/../init.php');
require_once(__DIR__ . '/paymentgateway/mpesa.php');

echo "Application loaded successfully\n\n";

// Get configuration
global $config;

$consumer_key = $config['mpesa_consumer_key'];
$consumer_secret = $config['mpesa_consumer_secret'];
$shortcode = $config['mpesa_shortcode'];
$passkey = $config['mpesa_passkey'];
$environment = $config['mpesa_environment'];

echo "Configuration:\n";
echo "  Environment: $environment\n";
echo "  Shortcode: $shortcode\n";
echo "  Consumer Key: " . substr($consumer_key, 0, 10) . "...\n\n";

// Step 1: Get OAuth token
echo "Step 1: Getting OAuth token...\n";
try {
    $access_token = mpesa_get_access_token();
    echo "✓ Token obtained: " . substr($access_token, 0, 20) . "...\n\n";
} catch (Exception $e) {
    die("✗ OAuth failed: " . $e->getMessage() . "\n");
}

// Step 2: Prepare STK Push request
echo "Step 2: Preparing STK Push request...\n";

$timestamp = date('YmdHis');
$password = mpesa_generate_password($shortcode, $passkey, $timestamp);

// Use a test phone number (replace with your actual test number)
$test_phone = '254708374149'; // Replace with your test number
$test_amount = 1; // Minimum amount for testing

$callback_url = 'https://webhook.site/unique-id'; // Use a real webhook URL for testing

$request_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $test_amount,
    'PartyA' => $test_phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $test_phone,
    'CallBackURL' => $callback_url,
    'AccountReference' => 'TEST123',
    'TransactionDesc' => 'Test Payment'
];

echo "Request data:\n";
echo json_encode($request_data, JSON_PRETTY_PRINT) . "\n\n";

// Step 3: Send STK Push request
echo "Step 3: Sending STK Push request...\n";

$base_url = mpesa_get_base_url();
$stk_push_url = $base_url . '/mpesa/stkpush/v1/processrequest';

echo "URL: $stk_push_url\n\n";

try {
    $response = Http::postJsonData($stk_push_url, $request_data, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    
    echo "=== RAW RESPONSE ===\n";
    echo $response . "\n\n";
    
    // Parse response
    $result = json_decode($response, true);
    
    echo "=== PARSED RESPONSE ===\n";
    print_r($result);
    echo "\n";
    
    // Check response
    if (isset($result['ResponseCode'])) {
        if ($result['ResponseCode'] == '0') {
            echo "✓ SUCCESS: STK Push initiated\n";
            echo "  CheckoutRequestID: " . $result['CheckoutRequestID'] . "\n";
            echo "  MerchantRequestID: " . $result['MerchantRequestID'] . "\n";
        } else {
            echo "✗ FAILED: ResponseCode = " . $result['ResponseCode'] . "\n";
            echo "  ResponseDescription: " . $result['ResponseDescription'] . "\n";
        }
    } elseif (isset($result['errorCode'])) {
        echo "✗ ERROR: " . $result['errorCode'] . "\n";
        echo "  Message: " . $result['errorMessage'] . "\n";
        
        // Analyze the error
        echo "\n=== ERROR ANALYSIS ===\n";
        if ($result['errorCode'] == '400.002.02') {
            echo "Error 400.002.02 means 'Bad Request - Invalid ShortCode'\n\n";
            echo "Possible causes:\n";
            echo "1. Shortcode '$shortcode' is not registered in your Daraja app\n";
            echo "2. Shortcode is for production but you're using sandbox (or vice versa)\n";
            echo "3. Shortcode doesn't have Lipa Na M-Pesa Online enabled\n";
            echo "4. Consumer Key/Secret don't match the shortcode\n\n";
            
            echo "ACTION REQUIRED:\n";
            echo "1. Log into https://developer.safaricom.co.ke/\n";
            echo "2. Go to your app (sandbox or production)\n";
            echo "3. Verify the shortcode matches: $shortcode\n";
            echo "4. Ensure 'Lipa Na M-Pesa Online' is enabled\n";
            echo "5. Check if you need to use a different shortcode for testing\n";
        }
    } else {
        echo "✗ UNEXPECTED RESPONSE FORMAT\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
