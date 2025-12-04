<?php
/**
 * Check recent M-Pesa related logs
 */

require_once('../init.php');

echo "=== Recent M-Pesa Logs ===\n\n";

$logs = ORM::for_table('tbl_logs')
    ->where_like('description', '%M-Pesa%')
    ->order_by_desc('id')
    ->limit(20)
    ->find_many();

if (count($logs) > 0) {
    foreach ($logs as $log) {
        echo "[{$log['date']}] {$log['type']}\n";
        echo "  {$log['description']}\n";
        echo "  IP: {$log['ip']}\n\n";
    }
} else {
    echo "No M-Pesa logs found\n\n";
}

echo "=== Recent Error Logs ===\n\n";

$error_logs = ORM::for_table('tbl_logs')
    ->where('type', 'M-Pesa')
    ->order_by_desc('id')
    ->limit(10)
    ->find_many();

if (count($error_logs) > 0) {
    foreach ($error_logs as $log) {
        echo "[{$log['date']}] {$log['description']}\n\n";
    }
} else {
    echo "No M-Pesa error logs found\n";
}
