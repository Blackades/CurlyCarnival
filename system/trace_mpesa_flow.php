<?php
/**
 * M-Pesa Complete Flow Tracer
 * 
 * This script traces the complete flow of M-Pesa configuration loading
 * and simulates what happens during an actual STK Push request.
 */

// Step 1: Load the application
echo "=== M-Pesa Complete Flow Tracer ===\n\n";

echo "Step 1: Loading application...\n";
$config_path = __DIR__ . '/../config.php';
if (!file_exists($config_path)) {
    die("ERROR: config.php not found at: $config_path\n");
}

require_once($config_path);
require_once(__DIR__ . '/../init.php');
require_once(__DIR__ . '/paymentgateway/mpesa.php');

echo "✓ Application loaded successfully\n\n";

// Step 2: Check global $config array
echo "Step 2: Checking global \$config array...\n";
global $config;

$mpesa_keys = ['mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode', 'mpesa_passkey', 'mpesa_environment'];

foreach ($mpesa_keys as $key) {
    if (isset($config[$key])) {
        $value = $config[$key];
        $length = strlen($value);
        $type = gettype($value);
        
        if ($key == 'mpesa_shortcode') {
            // Detailed analysis of shortcode
            echo "\n$key:\n";
            echo "  Raw value: '$value'\n";
            echo "  Length: $length\n";
            echo "  Type: $type\n";
            echo "  Is numeric: " . (is_numeric($value) ? 'YES' : 'NO') . "\n";
            echo "  Has spaces: " . (strpos($value, ' ') !== false ? 'YES' : 'NO') . "\n";
            echo "  Trimmed value: '" . trim($value) . "'\n";
            echo "  Trimmed length: " . strlen(trim($value)) . "\n";
            
            // Hex dump
            echo "  Hex dump: ";
            for ($i = 0; $i < strlen($value); $i++) {
                echo dechex(ord($value[$i])) . " ";
            }
            echo "\n";
            
            // Character analysis
            echo "  Characters:\n";
            for ($i = 0; $i < strlen($value); $i++) {
                $char = $value[$i];
                $ascii = ord($char);
                echo "    [$i] = '$char' (ASCII: $ascii)\n";
            }
        } else {
            // Mask sensitive data
            $masked = substr($value, 0, 4) . str_repeat('*', max(0, $length - 8)) . substr($value, -4);
            echo "$key: $masked (length: $length)\n";
        }
    } else {
        echo "$key: NOT SET\n";
    }
}

// Step 3: Test mpesa_get_base_url()
echo "\n\nStep 3: Testing mpesa_get_base_url()...\n";
try {
    $base_url = mpesa_get_base_url();
    echo "✓ Base URL: $base_url\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Step 4: Simulate password generation
echo "\n\nStep 4: Simulating password generation...\n";
$timestamp = date('YmdHis');
echo "Timestamp: $timestamp\n";

$shortcode = $config['mpesa_shortcode'];
$passkey = $config['mpesa_passkey'];

echo "Shortcode (raw): '$shortcode'\n";
echo "Shortcode (trimmed): '" . trim($shortcode) . "'\n";
echo "Passkey (first 10 chars): " . substr($passkey, 0, 10) . "...\n";

try {
    $password = mpesa_generate_password($shortcode, $passkey, $timestamp);
    echo "✓ Password generated successfully (length: " . strlen($password) . ")\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Step 5: Simulate OAuth token request
echo "\n\nStep 5: Testing OAuth token request...\n";
try {
    $access_token = mpesa_get_access_token();
    $token_preview = substr($access_token, 0, 20) . "..." . substr($access_token, -10);
    echo "✓ Access token obtained: $token_preview\n";
} catch (Exception $e) {
    echo "✗ OAuth Error: " . $e->getMessage() . "\n";
    echo "\nThis is expected if credentials are invalid.\n";
}

// Step 6: Simulate STK Push request payload
echo "\n\nStep 6: Simulating STK Push request payload...\n";

$test_phone = '254712345678';
$test_amount = 100;
$test_account = 'TEST_USER';
$test_description = 'Test Package';

$request_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $test_amount,
    'PartyA' => $test_phone,
    'PartyB' => $shortcode,
    'PhoneNumber' => $test_phone,
    'CallBackURL' => 'https://example.com/callback',
    'AccountReference' => $test_account,
    'TransactionDesc' => $test_description
];

echo "Request payload:\n";
echo json_encode($request_data, JSON_PRETTY_PRINT) . "\n";

// Step 7: Check for whitespace or encoding issues
echo "\n\nStep 7: Checking for whitespace/encoding issues...\n";

$shortcode_bytes = unpack('C*', $shortcode);
echo "Shortcode bytes: " . implode(' ', $shortcode_bytes) . "\n";

$expected_bytes = unpack('C*', '174379');
echo "Expected bytes:  " . implode(' ', $expected_bytes) . "\n";

if ($shortcode_bytes === $expected_bytes) {
    echo "✓ Shortcode bytes match expected value\n";
} else {
    echo "✗ Shortcode bytes DO NOT match - there may be hidden characters!\n";
}

// Step 8: Direct database check
echo "\n\nStep 8: Direct database check...\n";
try {
    $db_shortcode = ORM::for_table('tbl_appconfig')
        ->where('setting', 'mpesa_shortcode')
        ->find_one();
    
    if ($db_shortcode) {
        $db_value = $db_shortcode->value;
        echo "Database value: '$db_value'\n";
        echo "Database length: " . strlen($db_value) . "\n";
        echo "Database hex: ";
        for ($i = 0; $i < strlen($db_value); $i++) {
            echo dechex(ord($db_value[$i])) . " ";
        }
        echo "\n";
        
        echo "Config value: '$shortcode'\n";
        echo "Config length: " . strlen($shortcode) . "\n";
        
        if ($db_value === $shortcode) {
            echo "✓ Database and config values match exactly\n";
        } else {
            echo "✗ Database and config values DO NOT match!\n";
            echo "  This indicates the config loading process may be modifying the value.\n";
        }
    } else {
        echo "✗ mpesa_shortcode not found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}

// Step 9: Summary
echo "\n\n=== SUMMARY ===\n";
echo "If you see '400.002.02 invalid shortcode' error:\n";
echo "1. Check if shortcode bytes match expected (Step 7)\n";
echo "2. Check if database and config values match (Step 8)\n";
echo "3. Verify OAuth token can be obtained (Step 5)\n";
echo "4. Check the actual API request payload (Step 6)\n";
echo "\nNext step: Run an actual STK Push test to see the exact API response.\n";
