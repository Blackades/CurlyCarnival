<?php

/**
 * Check Actual Error - Examine the last failed transaction
 */

require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Check Last M-Pesa Transaction Error                          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Find the most recent M-Pesa transaction
$trx = ORM::for_table('tbl_payment_gateway')
    ->where('gateway', 'mpesa')
    ->order_by_desc('id')
    ->find_one();

if (!$trx) {
    echo "❌ No M-Pesa transactions found.\n";
    echo "Please try to purchase a package first.\n";
    exit(1);
}

echo "Last Transaction:\n";
echo "----------------------------------------\n";
echo "ID: {$trx['id']}\n";
echo "Username: {$trx['username']}\n";
echo "Plan: {$trx['plan_name']}\n";
echo "Amount: KES {$trx['price']}\n";
echo "Status: {$trx['status']} (1=Pending, 2=Paid, 3=Cancelled, 4=Failed)\n";
echo "Created: {$trx['created_date']}\n\n";

// Check if there's a request logged
if ($trx['pg_request']) {
    echo "Request Sent to M-Pesa:\n";
    echo "----------------------------------------\n";
    $request = json_decode($trx['pg_request'], true);
    if ($request) {
        echo json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        // Analyze the shortcode
        if (isset($request['BusinessShortCode'])) {
            $sc = $request['BusinessShortCode'];
            echo "BusinessShortCode Analysis:\n";
            echo "  Value: '{$sc}'\n";
            echo "  Type in array: " . gettype($sc) . "\n";
            echo "  Length: " . strlen($sc) . "\n";
            echo "  Is numeric: " . (is_numeric($sc) ? 'YES' : 'NO') . "\n";
            
            // Re-encode to see how it appears in JSON
            $test = ['BusinessShortCode' => $sc];
            $json = json_encode($test);
            echo "  JSON encoding: {$json}\n";
            
            if (strpos($json, '"BusinessShortCode":"') !== false) {
                echo "  ✓ Encoded as STRING\n";
            } else {
                echo "  ❌ Encoded as INTEGER\n";
            }
        }
    } else {
        echo "Failed to parse JSON\n";
        echo "Raw: {$trx['pg_request']}\n";
    }
} else {
    echo "⚠️  No request data logged\n";
}

// Check if there's a response
if ($trx['pg_paid_response']) {
    echo "\nResponse from M-Pesa:\n";
    echo "----------------------------------------\n";
    $response = json_decode($trx['pg_paid_response'], true);
    if ($response) {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        // Check for specific error
        if (isset($response['errorCode'])) {
            echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
            echo "║                              ERROR DETAILS                                 ║\n";
            echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
            echo "Error Code: {$response['errorCode']}\n";
            echo "Error Message: {$response['errorMessage']}\n\n";
            
            if ($response['errorCode'] == '400.002.02') {
                echo "This is the 'Invalid BusinessShortCode' error.\n\n";
                echo "Possible causes:\n";
                echo "1. Shortcode doesn't match your Daraja app\n";
                echo "2. Shortcode is being sent as integer instead of string\n";
                echo "3. Wrong environment (sandbox vs production)\n";
                echo "4. Daraja app is not active or suspended\n";
            }
        }
    } else {
        echo "Failed to parse JSON\n";
        echo "Raw: {$trx['pg_paid_response']}\n";
    }
} else {
    echo "\n⚠️  No response data logged yet\n";
    echo "The transaction may still be pending or the request failed before getting a response.\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              CONFIGURATION CHECK                           ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "Current M-Pesa Configuration:\n";
echo "----------------------------------------\n";
echo "Shortcode: {$config['mpesa_shortcode']}\n";
echo "Environment: {$config['mpesa_environment']}\n";
echo "Consumer Key: " . substr($config['mpesa_consumer_key'], 0, 10) . "...\n";
echo "Consumer Secret: " . substr($config['mpesa_consumer_secret'], 0, 10) . "...\n";

echo "\n=== Check Complete ===\n";
