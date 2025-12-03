<?php

/**
 * M-Pesa Credentials Quick Test
 * 
 * Test M-Pesa credentials without database
 * Usage: php test_mpesa_credentials.php
 */

echo "=== M-Pesa Credentials Quick Test ===\n\n";

// Prompt for credentials
echo "Enter your M-Pesa credentials:\n\n";

echo "Consumer Key: ";
$consumer_key = trim(fgets(STDIN));

echo "Consumer Secret: ";
$consumer_secret = trim(fgets(STDIN));

echo "Business Shortcode: ";
$shortcode = trim(fgets(STDIN));

echo "Passkey: ";
$passkey = trim(fgets(STDIN));

echo "Environment (sandbox/production): ";
$environment = trim(fgets(STDIN));

echo "Test Phone Number (254XXXXXXXXX): ";
$phone = trim(fgets(STDIN));

if (empty($phone)) {
    $phone = '254708374149';
    echo "Using default: $phone\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "VALIDATING CREDENTIALS\n";
echo str_repeat("=", 80) . "\n\n";

// Validate shortcode
echo "Shortcode Validation:\n";
echo "  Value: '$shortcode'\n";
echo "  Length: " . strlen($shortcode) . " characters\n";

if (ctype_digit($shortcode)) {
    echo "  ✓ Numeric only\n";
} else {
    echo "  ❌ Contains non-numeric characters!\n";
    echo "     This will cause 400.002.02 error\n";
}

if (strlen($shortcode) >= 5 && strlen($shortcode) <= 10) {
    echo "  ✓ Length OK\n";
} else {
    echo "  ⚠️  Length unusual (expected 6-7 digits)\n";
}

if ($shortcode === trim($shortcode)) {
    echo "  ✓ No extra spaces\n";
} else {
    echo "  ❌ Has leading/trailing spaces!\n";
}

echo "\n";

// Determine base URL
$base_url = ($environment === 'production') 
    ? 'https://api.safaricom.co.ke' 
    : 'https://sandbox.safaricom.co.ke';

echo "Environment: $environment\n";
echo "Base URL: $base_url\n\n";

// Test OAuth
echo str_repeat("=", 80) . "\n";
echo "STEP 1: Testing OAuth (Consumer Key & Secret)\n";
echo str_repeat("=", 80) . "\n\n";

$auth_url = $base_url . '/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode($consumer_key . ':' . $consumer_secret);

$ch = curl_init($auth_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $response\n\n";

if ($http_code !== 200) {
    echo "❌ OAuth FAILED!\n";
    echo "Your Consumer Key or Consumer Secret is incorrect.\n";
    exit(1);
}

$auth_response = json_decode($response, true);
if (!isset($auth_response['access_token'])) {
    echo "❌ No access token received!\n";
    exit(1);
}

$access_token = $auth_response['access_token'];
echo "✓ OAuth SUCCESS! Access token obtained.\n\n";

// Test STK Push
echo str_repeat("=", 80) . "\n";
echo "STEP 2: Testing STK Push (Shortcode & Passkey)\n";
echo str_repeat("=", 80) . "\n\n";

$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

$stk_request = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => 1,
    'PartyA' => $phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $phone,
    'CallBackURL' => 'https://example.com/callback',
    'AccountReference' => 'TEST',
    'TransactionDesc' => 'Credential Test'
];

echo "Request:\n";
echo json_encode($stk_request, JSON_PRETTY_PRINT) . "\n\n";

$stk_url = $base_url . '/mpesa/stkpush/v1/processrequest';

$ch = curl_init($stk_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_request));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
echo "Response: $response\n\n";

$result = json_decode($response, true);

echo str_repeat("=", 80) . "\n";
echo "RESULT\n";
echo str_repeat("=", 80) . "\n\n";

if ($result && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
    echo "✓✓✓ SUCCESS! ✓✓✓\n\n";
    echo "All credentials are CORRECT!\n";
    echo "CheckoutRequestID: {$result['CheckoutRequestID']}\n";
    echo "MerchantRequestID: {$result['MerchantRequestID']}\n\n";
    echo "The phone should receive an STK push prompt.\n";
    
} else {
    echo "❌ FAILED\n\n";
    
    if ($result) {
        $error_code = $result['errorCode'] ?? $result['ResponseCode'] ?? 'Unknown';
        $error_message = $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'Unknown error';
        
        echo "Error Code: $error_code\n";
        echo "Error Message: $error_message\n\n";
        
        if ($error_code == '400.002.02' || strpos($error_message, 'BusinessShortCode') !== false) {
            echo "DIAGNOSIS: Invalid BusinessShortCode\n\n";
            echo "Your shortcode '$shortcode' is being rejected by M-Pesa.\n\n";
            echo "Common causes:\n";
            echo "1. Shortcode has non-numeric characters\n";
            echo "2. Shortcode doesn't match your Daraja app\n";
            echo "3. Using wrong environment (sandbox vs production)\n";
            echo "4. Shortcode not approved/active\n";
            echo "5. Using Paybill instead of Business Shortcode\n\n";
            
            echo "What to check:\n";
            echo "1. Log in to https://developer.safaricom.co.ke/\n";
            echo "2. Go to your app\n";
            echo "3. Find 'Lipa Na M-Pesa Online' section\n";
            echo "4. Copy the EXACT Business Shortcode shown\n";
            echo "5. Make sure it's numeric only (no spaces, letters, dashes)\n\n";
            
        } elseif ($error_code == '400.002.01') {
            echo "DIAGNOSIS: Invalid Credentials\n\n";
            echo "Your Consumer Key or Consumer Secret is wrong.\n";
            echo "Or your Passkey is incorrect.\n\n";
            
        } elseif ($error_code == '500.001.1001') {
            echo "DIAGNOSIS: Invalid Phone Number\n\n";
            echo "Phone must be in format: 254XXXXXXXXX\n";
        }
    }
}

echo "\n=== Test Complete ===\n";
