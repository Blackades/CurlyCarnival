<?php
/**
 * Check Daraja App Configuration
 * 
 * This script helps verify your Daraja app settings
 */

echo "=== Daraja App Configuration Checker ===\n\n";

// Load application
$config_path = __DIR__ . '/../config.php';
require_once($config_path);
require_once(__DIR__ . '/../init.php');

global $config;

$consumer_key = $config['mpesa_consumer_key'];
$consumer_secret = $config['mpesa_consumer_secret'];
$shortcode = $config['mpesa_shortcode'];
$environment = isset($config['mpesa_environment']) ? $config['mpesa_environment'] : 'sandbox';

echo "Current Configuration:\n";
echo "  Environment: $environment\n";
echo "  Shortcode: $shortcode\n";
echo "  Consumer Key: " . substr($consumer_key, 0, 10) . "...\n";
echo "  Consumer Secret: " . substr($consumer_secret, 0, 10) . "...\n\n";

echo "=== IMPORTANT INFORMATION ===\n\n";

echo "For SANDBOX testing:\n";
echo "  - You should use the TEST shortcode: 174379\n";
echo "  - This is Safaricom's test paybill number\n";
echo "  - Your current shortcode: $shortcode\n";
if ($shortcode == '174379') {
    echo "  ✓ You are using the correct test shortcode\n";
} else {
    echo "  ✗ WARNING: You are NOT using the standard test shortcode!\n";
}
echo "\n";

echo "For PRODUCTION:\n";
echo "  - You must use YOUR ACTUAL paybill/till number\n";
echo "  - This must be registered in your Daraja production app\n";
echo "  - You cannot use 174379 in production\n\n";

echo "=== COMMON ISSUES ===\n\n";

echo "Error 400.002.02 (Invalid ShortCode) usually means:\n\n";

echo "1. SHORTCODE MISMATCH\n";
echo "   - Your app's consumer key/secret don't match the shortcode\n";
echo "   - Solution: Verify in Daraja portal that shortcode $shortcode is linked to your app\n\n";

echo "2. WRONG ENVIRONMENT\n";
echo "   - Using sandbox credentials with production shortcode (or vice versa)\n";
echo "   - Current environment: $environment\n";
echo "   - Solution: Ensure shortcode matches environment\n\n";

echo "3. LIPA NA M-PESA NOT ENABLED\n";
echo "   - Your shortcode doesn't have Lipa Na M-Pesa Online enabled\n";
echo "   - Solution: Contact Safaricom to enable this feature\n\n";

echo "4. INCORRECT PASSKEY\n";
echo "   - The passkey doesn't match the shortcode\n";
echo "   - Solution: Get correct passkey from Daraja portal\n\n";

echo "=== NEXT STEPS ===\n\n";

echo "1. Log into: https://developer.safaricom.co.ke/\n";
echo "2. Go to: My Apps > [Your App Name]\n";
echo "3. Check the 'Test Credentials' or 'Production Credentials' section\n";
echo "4. Verify these match:\n";
echo "   - Consumer Key: " . substr($consumer_key, 0, 15) . "...\n";
echo "   - Consumer Secret: " . substr($consumer_secret, 0, 15) . "...\n";
echo "   - Shortcode: $shortcode\n";
echo "   - Passkey: " . substr($config['mpesa_passkey'], 0, 15) . "...\n\n";

echo "5. If using SANDBOX:\n";
echo "   - Shortcode MUST be: 174379\n";
echo "   - Use the test credentials from Daraja\n\n";

echo "6. If using PRODUCTION:\n";
echo "   - Shortcode must be YOUR paybill/till number\n";
echo "   - Must be registered with Safaricom\n";
echo "   - Must have Lipa Na M-Pesa Online enabled\n\n";

echo "=== RECOMMENDATION ===\n\n";

if ($environment == 'sandbox' && $shortcode != '174379') {
    echo "⚠️  You are in SANDBOX mode but not using the standard test shortcode!\n";
    echo "    Consider changing shortcode to: 174379\n";
    echo "    Or verify your custom sandbox shortcode is properly configured.\n\n";
}

if ($environment == 'production' && $shortcode == '174379') {
    echo "⚠️  You are in PRODUCTION mode but using the test shortcode!\n";
    echo "    You MUST use your actual paybill/till number for production.\n\n";
}

echo "Would you like to:\n";
echo "A) Test with the standard sandbox shortcode (174379)\n";
echo "B) Verify your current shortcode configuration\n";
echo "C) Check if OAuth works with current credentials\n\n";

echo "Run: php test_actual_stk_push.php to see the exact API error\n";
