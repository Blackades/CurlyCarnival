<?php

/**
 * Debug Purchase Flow - Trace M-Pesa Configuration During Purchase
 * 
 * This script simulates the purchase flow to identify where the shortcode issue occurs
 */

// Include the application initialization
require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              M-Pesa Purchase Flow Debug Script                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Step 1: Checking global \$config array...\n";
echo "----------------------------------------\n";

// Check if $config is available
if (!isset($config)) {
    echo "❌ ERROR: \$config array is not defined!\n";
    exit(1);
}

// Check M-Pesa settings in $config
$mpesa_settings = [
    'mpesa_consumer_key',
    'mpesa_consumer_secret',
    'mpesa_shortcode',
    'mpesa_passkey',
    'mpesa_environment'
];

foreach ($mpesa_settings as $setting) {
    if (isset($config[$setting])) {
        $value = $config[$setting];
        if (in_array($setting, ['mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_passkey'])) {
            // Mask sensitive data
            $masked = substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
            echo "✓ {$setting}: {$masked} (length: " . strlen($value) . ")\n";
        } else {
            echo "✓ {$setting}: {$value} (length: " . strlen($value) . ")\n";
        }
    } else {
        echo "❌ {$setting}: NOT SET\n";
    }
}

echo "\n";
echo "Step 2: Checking database values directly...\n";
echo "----------------------------------------\n";

// Query database directly
foreach ($mpesa_settings as $setting) {
    $d = ORM::for_table('tbl_appconfig')->where('setting', $setting)->find_one();
    if ($d) {
        $value = $d->value;
        if (in_array($setting, ['mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_passkey'])) {
            // Mask sensitive data
            $masked = substr($value, 0, 4) . str_repeat('*', max(0, strlen($value) - 8)) . substr($value, -4);
            echo "✓ {$setting}: {$masked} (length: " . strlen($value) . ")\n";
        } else {
            echo "✓ {$setting}: {$value} (length: " . strlen($value) . ")\n";
        }
    } else {
        echo "❌ {$setting}: NOT FOUND IN DATABASE\n";
    }
}

echo "\n";
echo "Step 3: Comparing \$config vs Database...\n";
echo "----------------------------------------\n";

$mismatches = [];
foreach ($mpesa_settings as $setting) {
    $config_value = isset($config[$setting]) ? $config[$setting] : null;
    
    $d = ORM::for_table('tbl_appconfig')->where('setting', $setting)->find_one();
    $db_value = $d ? $d->value : null;
    
    if ($config_value !== $db_value) {
        $mismatches[] = $setting;
        echo "⚠️  MISMATCH: {$setting}\n";
        echo "   Config: " . ($config_value ? substr($config_value, 0, 20) . '...' : 'NULL') . "\n";
        echo "   Database: " . ($db_value ? substr($db_value, 0, 20) . '...' : 'NULL') . "\n";
    } else {
        echo "✓ {$setting}: MATCH\n";
    }
}

echo "\n";
echo "Step 4: Testing STK Push request preparation...\n";
echo "----------------------------------------\n";

// Simulate what happens in mpesa_create_transaction
$shortcode = $config['mpesa_shortcode'];
$passkey = $config['mpesa_passkey'];
$timestamp = date('YmdHis');

echo "Shortcode from \$config: '{$shortcode}'\n";
echo "Shortcode type: " . gettype($shortcode) . "\n";
echo "Shortcode length: " . strlen($shortcode) . "\n";
echo "Shortcode is numeric: " . (is_numeric($shortcode) ? 'YES' : 'NO') . "\n";
echo "Shortcode trimmed: '" . trim($shortcode) . "'\n";
echo "Shortcode has whitespace: " . (trim($shortcode) !== $shortcode ? 'YES' : 'NO') . "\n";

// Check for hidden characters
echo "Shortcode hex dump: " . bin2hex($shortcode) . "\n";

echo "\n";
echo "Step 5: Simulating STK Push request data...\n";
echo "----------------------------------------\n";

$request_data = [
    'BusinessShortCode' => $shortcode,
    'Password' => base64_encode($shortcode . $passkey . $timestamp),
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => 10,
    'PartyA' => '254706882144',
    'PartyB' => $shortcode,
    'PhoneNumber' => '254706882144',
    'CallBackURL' => U . 'callback/mpesa',
    'AccountReference' => 'TEST',
    'TransactionDesc' => 'Test Payment'
];

echo "Request JSON:\n";
echo json_encode($request_data, JSON_PRETTY_PRINT) . "\n";

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              SUMMARY                                       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

if (count($mismatches) > 0) {
    echo "⚠️  WARNING: Found " . count($mismatches) . " mismatch(es) between \$config and database:\n";
    foreach ($mismatches as $setting) {
        echo "   - {$setting}\n";
    }
    echo "\nRECOMMENDATION: Clear application cache or restart PHP-FPM\n";
} else {
    echo "✓ All settings match between \$config and database\n";
}

if (!is_numeric($shortcode) || strlen($shortcode) != 6) {
    echo "\n❌ ISSUE FOUND: Shortcode is invalid!\n";
    echo "   Expected: 6-digit numeric value (e.g., 174379)\n";
    echo "   Actual: '{$shortcode}' (length: " . strlen($shortcode) . ")\n";
    echo "\nRECOMMENDATION: Check for whitespace, special characters, or data type issues\n";
} else {
    echo "\n✓ Shortcode appears valid: {$shortcode}\n";
}

echo "\n=== Debug Complete ===\n";
