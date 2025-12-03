<?php

/**
 * M-Pesa STK Push Test Script
 * 
 * This script tests M-Pesa credentials by initiating an STK Push
 * Shows detailed request/response for debugging
 * 
 * Usage: php test_mpesa_stk_push.php
 */

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    M-Pesa STK Push Test Script                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Load config
$config_path = dirname(__DIR__) . '/config.php';
if (!file_exists($config_path)) {
    echo "ERROR: config.php not found at $config_path\n";
    exit(1);
}

require_once $config_path;

// Get database connection
try {
    $db_host = isset($db_host) && $db_host === 'localhost' ? '127.0.0.1' : $db_host;
    $dsn = "mysql:host=$db_host;dbname=$db_name";
    if (!empty($db_port)) {
        $dsn .= ";port=$db_port";
    }
    
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "ERROR: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get M-Pesa configuration from database
echo "Step 1: Loading M-Pesa configuration from database...\n";

$mpesa_settings = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

$config = [];
$stmt = $pdo->prepare("SELECT setting, value FROM tbl_appconfig WHERE setting = ?");

foreach ($mpesa_settings as $setting) {
    $stmt->execute([$setting]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $key = str_replace('mpesa_', '', $setting);
        $config[$key] = $row['value'];
        
        // Show masked value
        $value = $row['value'];
        $length = strlen($value);
        
        if ($setting === 'mpesa_shortcode') {
            echo "  ✓ $setting: $value (length: $length)\n";
            
            // Validate shortcode
            if (!ctype_digit($value)) {
                echo "    ⚠️  WARNING: Shortcode contains non-numeric characters!\n";
            }
            if ($length < 5 || $length > 10) {
                echo "    ⚠️  WARNING: Shortcode length unusual (expected 6-7 digits)\n";
            }
        } elseif ($setting === 'mpesa_environment') {
            echo "  ✓ $setting: $value\n";
        } else {
            $masked = $length > 8 ? substr($value, 0, 4) . str_repeat('*', min($length - 8, 20)) . substr($value, -4) : str_repeat('*', $length);
            echo "  ✓ $setting: $masked (length: $length)\n";
            
            if ($length < 30 && $setting !== 'mpesa_environment') {
                echo "    ⚠️  WARNING: Value seems too short (may be truncated)\n";
            }
        }
    } else {
        echo "  ❌ $setting: NOT SET\n";
        $config[str_replace('mpesa_', '', $setting)] = null;
    }
}

// Check if all required settings are present
$missing = [];
foreach (['consumer_key', 'consumer_secret', 'shortcode', 'passkey', 'environment'] as $key) {
    if (empty($config[$key])) {
        $missing[] = $key;
    }
}

if (!empty($missing)) {
    echo "\n❌ ERROR: Missing required settings: " . implode(', ', $missing) . "\n";
    echo "\nPlease configure M-Pesa in Admin Panel > Payment Gateway > M-Pesa\n";
    exit(1);
}

echo "\n✓ All M-Pesa settings found\n\n";

// Determine API base URL
$base_url = ($config['environment'] === 'production') 
    ? 'https://api.safaricom.co.ke' 
    : 'https://sandbox.safaricom.co.ke';

echo "Step 2: Getting OAuth access token...\n";
echo "  Environment: {$config['environment']}\n";
echo "  Base URL: $base_url\n";

// Get OAuth token
$auth_url = $base_url . '/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode($config['consumer_key'] . ':' . $config['consumer_secret']);

$ch = curl_init($auth_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  HTTP Code: $http_code\n";

if ($http_code !== 200) {
    echo "\n❌ OAuth Failed!\n";
    echo "Response: $response\n\n";
    echo "Possible issues:\n";
    echo "  - Invalid Consumer Key or Consumer Secret\n";
    echo "  - Credentials expired or revoked\n";
    echo "  - Wrong environment (sandbox vs production)\n";
    exit(1);
}

$auth_response = json_decode($response, true);
if (!isset($auth_response['access_token'])) {
    echo "\n❌ No access token in response!\n";
    echo "Response: $response\n";
    exit(1);
}

$access_token = $auth_response['access_token'];
echo "  ✓ Access token obtained: " . substr($access_token, 0, 20) . "...\n\n";

// Prepare STK Push request
echo "Step 3: Preparing STK Push request...\n";

$timestamp = date('YmdHis');
$password = base64_encode($config['shortcode'] . $config['passkey'] . $timestamp);

// Test phone number (use sandbox test number or prompt for real number)
echo "\nEnter test phone number (format: 254XXXXXXXXX): ";
$phone = trim(fgets(STDIN));

if (empty($phone)) {
    $phone = '254708374149'; // Sandbox test number
    echo "Using sandbox test number: $phone\n";
}

// Validate phone format
if (!preg_match('/^254\d{9}$/', $phone)) {
    echo "⚠️  WARNING: Phone number format may be invalid (expected: 254XXXXXXXXX)\n";
}

$amount = 1; // Test with 1 KES

$stk_request = [
    'BusinessShortCode' => $config['shortcode'],
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $config['shortcode'],
    'PhoneNumber' => $phone,
    'CallBackURL' => 'https://example.com/callback', // Dummy callback for testing
    'AccountReference' => 'TEST',
    'TransactionDesc' => 'Test Payment'
];

echo "\nRequest Details:\n";
echo "  BusinessShortCode: {$config['shortcode']}\n";
echo "  Timestamp: $timestamp\n";
echo "  Amount: $amount\n";
echo "  Phone: $phone\n";
echo "  Password (first 20 chars): " . substr($password, 0, 20) . "...\n\n";

// Show full request JSON
echo "Full Request JSON:\n";
echo json_encode($stk_request, JSON_PRETTY_PRINT) . "\n\n";

echo "Step 4: Sending STK Push request...\n";

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

echo "  HTTP Code: $http_code\n\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              RESPONSE                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Raw Response:\n";
echo $response . "\n\n";

$result = json_decode($response, true);

if ($result) {
    echo "Parsed Response:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if (isset($result['ResponseCode'])) {
        if ($result['ResponseCode'] == '0') {
            echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
            echo "║                              SUCCESS!                                      ║\n";
            echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
            
            echo "✓ STK Push initiated successfully!\n";
            echo "✓ CheckoutRequestID: {$result['CheckoutRequestID']}\n";
            echo "✓ MerchantRequestID: {$result['MerchantRequestID']}\n";
            echo "✓ Customer Description: {$result['CustomerMessage']}\n\n";
            
            echo "Your M-Pesa credentials are CORRECT!\n";
            echo "The phone should receive an STK push prompt.\n\n";
            
        } else {
            echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
            echo "║                              FAILED                                        ║\n";
            echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
            
            $error_code = $result['errorCode'] ?? $result['ResponseCode'];
            $error_message = $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'Unknown error';
            
            echo "❌ STK Push Failed\n";
            echo "Error Code: $error_code\n";
            echo "Error Message: $error_message\n\n";
            
            echo "Common Error Codes:\n";
            echo "  400.002.02 - Invalid BusinessShortCode\n";
            echo "  400.002.01 - Invalid credentials\n";
            echo "  500.001.1001 - Invalid phone number\n\n";
            
            diagnose_error($error_code, $error_message, $config);
        }
    } else {
        echo "❌ Unexpected response format\n";
    }
} else {
    echo "❌ Failed to parse JSON response\n";
}

echo "\n=== Test Complete ===\n";

/**
 * Diagnose common errors
 */
function diagnose_error($code, $message, $config) {
    echo "Diagnosis:\n";
    
    if ($code == '400.002.02' || strpos($message, 'BusinessShortCode') !== false) {
        echo "\n🔍 Invalid BusinessShortCode Error\n\n";
        echo "Your shortcode: {$config['shortcode']}\n";
        echo "Length: " . strlen($config['shortcode']) . " characters\n\n";
        
        echo "Checks:\n";
        
        // Check if numeric
        if (!ctype_digit($config['shortcode'])) {
            echo "  ❌ Shortcode contains non-numeric characters\n";
            echo "     Fix: Remove any letters, spaces, or special characters\n";
        } else {
            echo "  ✓ Shortcode is numeric\n";
        }
        
        // Check length
        if (strlen($config['shortcode']) < 5 || strlen($config['shortcode']) > 10) {
            echo "  ❌ Shortcode length is unusual\n";
            echo "     Expected: 6-7 digits (e.g., 174379 or 600XXX)\n";
        } else {
            echo "  ✓ Shortcode length looks OK\n";
        }
        
        // Check for spaces
        if ($config['shortcode'] !== trim($config['shortcode'])) {
            echo "  ❌ Shortcode has leading/trailing spaces\n";
            echo "     Fix: Remove spaces\n";
        } else {
            echo "  ✓ No extra spaces\n";
        }
        
        echo "\nPossible Solutions:\n";
        echo "1. Verify shortcode in Daraja portal\n";
        echo "2. Ensure you're using Business Shortcode (not Paybill)\n";
        echo "3. Check environment matches (sandbox vs production)\n";
        echo "4. Try copying shortcode again from Daraja\n";
        echo "5. Check if shortcode is active/approved\n\n";
        
        echo "To fix in database:\n";
        echo "  UPDATE tbl_appconfig SET value = 'YOUR_CORRECT_SHORTCODE' WHERE setting = 'mpesa_shortcode';\n\n";
        
    } elseif ($code == '400.002.01') {
        echo "\n🔍 Invalid Credentials Error\n\n";
        echo "Possible issues:\n";
        echo "  - Consumer Key or Consumer Secret is wrong\n";
        echo "  - Credentials expired or revoked\n";
        echo "  - Wrong environment (sandbox vs production)\n\n";
        
        echo "Solutions:\n";
        echo "1. Log in to Daraja portal\n";
        echo "2. Regenerate Consumer Key and Secret\n";
        echo "3. Update in Admin Panel\n";
        echo "4. Ensure environment matches\n\n";
        
    } elseif ($code == '500.001.1001') {
        echo "\n🔍 Invalid Phone Number Error\n\n";
        echo "Phone format must be: 254XXXXXXXXX\n";
        echo "Example: 254708374149\n\n";
    }
}
