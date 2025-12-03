<?php

/**
 * Verify Shortcode Fix - Confirm shortcode is sent as string
 */

require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Verify M-Pesa Shortcode Fix                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Simulate the fixed code
$shortcode_from_config = $config['mpesa_shortcode'];
$shortcode_fixed = (string)$config['mpesa_shortcode'];

echo "Before Fix:\n";
echo "----------------------------------------\n";
echo "Value: '{$shortcode_from_config}'\n";
echo "Type: " . gettype($shortcode_from_config) . "\n";

$test_array_before = [
    'BusinessShortCode' => $shortcode_from_config,
    'PartyB' => $shortcode_from_config
];

$json_before = json_encode($test_array_before);
echo "JSON Output: {$json_before}\n";

// Check if it's encoded as integer
if (strpos($json_before, '"BusinessShortCode":' . $shortcode_from_config) !== false) {
    echo "❌ WARNING: Shortcode encoded as INTEGER (no quotes)\n";
} else {
    echo "✓ Shortcode encoded as STRING (with quotes)\n";
}

echo "\nAfter Fix:\n";
echo "----------------------------------------\n";
echo "Value: '{$shortcode_fixed}'\n";
echo "Type: " . gettype($shortcode_fixed) . "\n";

$test_array_after = [
    'BusinessShortCode' => $shortcode_fixed,
    'PartyB' => $shortcode_fixed
];

$json_after = json_encode($test_array_after);
echo "JSON Output: {$json_after}\n";

// Check if it's encoded as string
if (strpos($json_after, '"BusinessShortCode":"' . $shortcode_fixed . '"') !== false) {
    echo "✓ Shortcode encoded as STRING (with quotes)\n";
} else {
    echo "❌ WARNING: Shortcode still encoded as INTEGER\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              RESULT                                        ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";

if (strpos($json_after, '"BusinessShortCode":"' . $shortcode_fixed . '"') !== false) {
    echo "\n✓ FIX SUCCESSFUL!\n";
    echo "The shortcode will now be sent as a string to M-Pesa API.\n";
    echo "This should resolve the '400.002.02 invalid short code' error.\n";
} else {
    echo "\n❌ FIX MAY NOT WORK\n";
    echo "The shortcode is still being encoded as an integer.\n";
    echo "Additional investigation needed.\n";
}

echo "\n=== Verification Complete ===\n";
