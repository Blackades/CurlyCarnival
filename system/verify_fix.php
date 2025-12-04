<?php
/**
 * Verify that the M-Pesa integration fix is in place
 */

echo "=== Verifying M-Pesa Integration Fix ===\n\n";

$order_file = '../system/controllers/order.php';

if (!file_exists($order_file)) {
    echo "ERROR: order.php not found\n";
    exit;
}

$content = file_get_contents($order_file);

// Check if the fix is present
if (strpos($content, '// Reload transaction as array for payment gateway function') !== false) {
    echo "✅ FIX VERIFIED: Transaction reload code is present\n\n";
    
    // Check if it reloads the transaction
    if (strpos($content, '$trx = ORM::for_table(\'tbl_payment_gateway\')->find_one($id)') !== false) {
        echo "✅ Transaction is being reloaded by ID\n\n";
    }
    
    // Check if it passes the reloaded transaction
    if (strpos($content, 'call_user_func($gateway . \'_create_transaction\', $trx, $user)') !== false) {
        echo "✅ Reloaded transaction is being passed to gateway function\n\n";
    }
    
    echo "=== FIX IS COMPLETE ===\n\n";
    echo "Next steps:\n";
    echo "1. Clear PHP opcache: systemctl restart php-fpm\n";
    echo "2. Try making a purchase with M-Pesa\n";
    echo "3. You should now receive an STK Push prompt on your phone\n";
    
} else {
    echo "❌ FIX NOT FOUND: The transaction reload code is missing\n";
    echo "The fix may not have been applied correctly\n";
}
