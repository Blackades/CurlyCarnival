<?php

/**
 * Check Database Schema - Show tbl_payment_gateway structure
 */

require_once '../init.php';

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Check Payment Gateway Table Schema                           ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Get table structure
$result = ORM::for_table('tbl_payment_gateway')->raw_query("SHOW COLUMNS FROM tbl_payment_gateway")->find_many();

echo "Table: tbl_payment_gateway\n";
echo "----------------------------------------\n";
echo sprintf("%-25s %-20s %-10s\n", "Field", "Type", "Null");
echo str_repeat("-", 60) . "\n";

foreach ($result as $column) {
    echo sprintf("%-25s %-20s %-10s\n", 
        $column['Field'], 
        $column['Type'], 
        $column['Null']
    );
}

echo "\n";
echo "Recent Transactions:\n";
echo "----------------------------------------\n";

// Get recent transactions
$transactions = ORM::for_table('tbl_payment_gateway')
    ->order_by_desc('id')
    ->limit(5)
    ->find_many();

if (count($transactions) > 0) {
    foreach ($transactions as $trx) {
        echo "ID: {$trx['id']} | User: {$trx['username']} | Status: {$trx['status']} | Created: {$trx['created_date']}\n";
        
        // Check which column contains 'mpesa'
        foreach ($trx->as_array() as $key => $value) {
            if (stripos($value, 'mpesa') !== false) {
                echo "  -> Found 'mpesa' in column '{$key}': {$value}\n";
            }
        }
    }
} else {
    echo "No transactions found\n";
}

echo "\n=== Schema Check Complete ===\n";
