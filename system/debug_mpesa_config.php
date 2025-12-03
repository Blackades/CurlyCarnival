<?php

/**
 * Debug M-Pesa Configuration
 * 
 * Shows exactly what the application sees when it loads M-Pesa config
 */

// Load PHPNuxBill system
require_once '../init.php';

echo "=== M-Pesa Configuration Debug ===\n\n";

echo "Step 1: Checking global \$config array...\n\n";

$mpesa_keys = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

foreach ($mpesa_keys as $key) {
    if (isset($config[$key])) {
        $value = $config[$key];
        $length = strlen($value);
        
        if ($key === 'mpesa_shortcode') {
            echo "$key:\n";
            echo "  Value: '$value'\n";
            echo "  Length: $length\n";
            echo "  Type: " . gettype($value) . "\n";
            echo "  Is numeric: " . (ctype_digit($value) ? 'YES' : 'NO') . "\n";
            echo "  Has spaces: " . ($value !== trim($value) ? 'YES' : 'NO') . "\n";
            echo "  Hex dump: " . bin2hex($value) . "\n";
            
            // Check each character
            echo "  Characters:\n";
            for ($i = 0; $i < $length; $i++) {
                $char = $value[$i];
                $ascii = ord($char);
                echo "    [$i] = '$char' (ASCII: $ascii)\n";
            }
        } elseif ($key === 'mpesa_environment') {
            echo "$key: $value\n";
        } else {
            $masked = $length > 8 ? substr($value, 0, 4) . str_repeat('*', min($length - 8, 20)) . substr($value, -4) : str_repeat('*', $length);
            echo "$key: $masked (length: $length)\n";
        }
        echo "\n";
    } else {
        echo "$key: NOT SET\n\n";
    }
}

echo "Step 2: Testing mpesa_get_base_url()...\n";
try {
    $base_url = mpesa_get_base_url();
    echo "  Base URL: $base_url\n\n";
} catch (Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n\n";
}

echo "Step 3: Testing mpesa_get_access_token()...\n";
try {
    $access_token = mpesa_get_access_token();
    echo "  ✓ Access token obtained: " . substr($access_token, 0, 20) . "...\n\n";
} catch (Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
}

echo "Step 4: Simulating STK Push request...\n";

$shortcode = $config['mpesa_shortcode'];
$passkey = $config['mpesa_passkey'];
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

echo "  Shortcode: '$shortcode'\n";
echo "  Shortcode length: " . strlen($shortcode) . "\n";
echo "  Shortcode is numeric: " . (ctype_digit($shortcode) ? 'YES' : 'NO') . "\n";
echo "  Timestamp: $timestamp\n";
echo "  Password (first 20): " . substr($password, 0, 20) . "...\n\n";

$request_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => 1,
    'PartyA' => '254708374149',
    'PartyB' => $shortcode,
    'PhoneNumber' => '254708374149',
    'CallBackURL' => 'https://example.com/callback',
    'AccountReference' => 'DEBUG',
    'TransactionDesc' => 'Debug Test'
];

echo "Request JSON:\n";
echo json_encode($request_data, JSON_PRETTY_PRINT) . "\n\n";

echo "Step 5: Checking for hidden characters...\n";

// Check if shortcode has any non-printable characters
$clean_shortcode = preg_replace('/[^0-9]/', '', $shortcode);
if ($shortcode !== $clean_shortcode) {
    echo "  ⚠️  WARNING: Shortcode contains non-numeric characters!\n";
    echo "  Original: '$shortcode'\n";
    echo "  Cleaned: '$clean_shortcode'\n";
    echo "  Difference: " . strlen($shortcode) - strlen($clean_shortcode) . " characters\n\n";
    
    echo "  FIX: Run this SQL to clean the shortcode:\n";
    echo "  UPDATE tbl_appconfig SET value = '$clean_shortcode' WHERE setting = 'mpesa_shortcode';\n\n";
} else {
    echo "  ✓ Shortcode is clean (numeric only)\n\n";
}

echo "=== Debug Complete ===\n";
