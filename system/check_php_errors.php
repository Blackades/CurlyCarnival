<?php

/**
 * Check PHP Error Logs
 */

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Check PHP Error Logs                                          ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// Common PHP error log locations
$log_locations = [
    '/var/log/php-fpm/error.log',
    '/var/log/php_errors.log',
    '/var/log/httpd/error_log',
    '/var/log/apache2/error.log',
    '../system/logs/error.log',
    'logs/error.log'
];

echo "Checking common log locations...\n";
echo "----------------------------------------\n";

foreach ($log_locations as $log) {
    if (file_exists($log)) {
        echo "✓ Found: {$log}\n";
        echo "Last 20 lines:\n";
        echo str_repeat("-", 60) . "\n";
        $lines = `tail -20 {$log}`;
        echo $lines;
        echo str_repeat("-", 60) . "\n\n";
    }
}

// Check application logs
echo "\nChecking application logs directory...\n";
echo "----------------------------------------\n";

$app_log_dir = '../system/logs';
if (is_dir($app_log_dir)) {
    $files = scandir($app_log_dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strpos($file, '.log') !== false) {
            $log_file = $app_log_dir . '/' . $file;
            echo "\n✓ Found: {$log_file}\n";
            echo "Last 10 lines:\n";
            echo str_repeat("-", 60) . "\n";
            $lines = `tail -10 {$log_file}`;
            echo $lines;
            echo str_repeat("-", 60) . "\n";
        }
    }
} else {
    echo "❌ Application logs directory not found\n";
}

echo "\n=== Log Check Complete ===\n";
