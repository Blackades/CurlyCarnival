<?php

/**
 * Check Transaction 38 - The most recent pending transaction
 */

require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Check Transaction ID 38                                       ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

$trx = ORM::for_table('tbl_payment_gateway')->find_one(38);

if (!$trx) {
    echo "❌ Transaction 38 not found\n";
    exit(1);
}

echo "Transaction Details:\n";
echo "----------------------------------------\n";
echo "ID: {$trx['id']}\n";
echo "Username: {$trx['username']}\n";
echo "Gateway: {$trx['gateway']}\n";
echo "Plan: {$trx['plan_name']}\n";
echo "Amount: KES {$trx['price']}\n";
echo "Status: {$trx['status']} (1=Pending, 2=Paid, 3=Cancelled, 4=Failed)\n";
echo "Created: {$trx['created_date']}\n";
echo "CheckoutRequestID: {$trx['gateway_trx_id']}\n\n";

if ($trx['pg_request']) {
    echo "Request Sent to M-Pesa:\n";
    echo "----------------------------------------\n";
    $request = json_decode($trx['pg_request'], true);
    if ($request) {
        echo json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        // Check the shortcode type
        if (isset($request['BusinessShortCode'])) {
            $sc = $request['BusinessShortCode'];
            echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
            echo "║                    SHORTCODE ANALYSIS                                      ║\n";
            echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
            echo "BusinessShortCode: '{$sc}'\n";
            echo "Type: " . gettype($sc) . "\n";
            echo "Length: " . strlen($sc) . "\n\n";
            
            // Re-encode to check JSON format
            $test = ['BusinessShortCode' => $sc, 'PartyB' => $request['PartyB']];
            $json = json_encode($test);
            echo "JSON Test: {$json}\n\n";
            
            if (strpos($json, '"BusinessShortCode":"') !== false) {
                echo "✓ BusinessShortCode is encoded as STRING\n";
                echo "✓ This is CORRECT - M-Pesa should accept this\n\n";
            } else {
                echo "❌ BusinessShortCode is encoded as INTEGER\n";
                echo "❌ This will cause the 'invalid short code' error\n\n";
            }
            
            if (strpos($json, '"PartyB":"') !== false) {
                echo "✓ PartyB is encoded as STRING\n";
            } else {
                echo "❌ PartyB is encoded as INTEGER\n";
            }
        }
    }
} else {
    echo "⚠️  No request data saved\n";
}

if ($trx['pg_paid_response']) {
    echo "\n";
    echo "Response from M-Pesa:\n";
    echo "----------------------------------------\n";
    $response = json_decode($trx['pg_paid_response'], true);
    if ($response) {
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        
        if (isset($response['errorCode'])) {
            echo "❌ ERROR: {$response['errorCode']} - {$response['errorMessage']}\n";
        } elseif (isset($response['ResponseCode'])) {
            if ($response['ResponseCode'] == '0') {
                echo "✓ SUCCESS: STK Push initiated\n";
            } else {
                echo "⚠️  Response Code: {$response['ResponseCode']}\n";
            }
        }
    }
} else {
    echo "\n⚠️  No response data yet (transaction still pending)\n";
}

echo "\n=== Check Complete ===\n";
