<?php

/**
 * Database Connection Diagnostic Script
 * 
 * This script helps diagnose database connection issues
 * and provides solutions for common problems.
 */

echo "=== Database Connection Diagnostic ===\n\n";

// Determine the correct path to config.php
$config_file = dirname(__DIR__) . '/config.php';
$config_sample = dirname(__DIR__) . '/config.sample.php';

// If not found, try parent directory
if (!file_exists($config_file)) {
    $config_file = dirname(dirname(__DIR__)) . '/config.php';
    $config_sample = dirname(dirname(__DIR__)) . '/config.sample.php';
}

echo "Step 1: Checking configuration files...\n";
echo "  Looking in: " . dirname($config_file) . "\n";

if (!file_exists($config_file)) {
    echo "  ❌ config.php NOT FOUND\n";
    if (file_exists($config_sample)) {
        echo "  ℹ️  Found: config.sample.php\n\n";
    } else {
        echo "  ❌ config.sample.php also NOT FOUND\n\n";
    }
    
    echo "ACTION REQUIRED:\n";
    echo "You need to create config.php from config.sample.php\n\n";
    
    echo "Run these commands:\n";
    echo "  cd " . dirname(__DIR__) . "\n";
    echo "  cp config.sample.php config.php\n";
    echo "  nano config.php  # or use vi, vim, etc.\n\n";
    
    echo "Then edit config.php and set:\n";
    echo "  \$db_host = '127.0.0.1';  # Use 127.0.0.1 instead of localhost\n";
    echo "  \$db_user = 'your_db_user';\n";
    echo "  \$db_pass = 'your_db_password';\n";
    echo "  \$db_name = 'your_db_name';\n\n";
    
    echo "After creating config.php, run this script again.\n";
    exit(1);
}

echo "  ✓ config.php found\n\n";

// Load config
require_once $config_file;

echo "Step 2: Checking database configuration...\n";
echo "  Host: $db_host\n";
echo "  Port: " . (empty($db_port) ? '(default 3306)' : $db_port) . "\n";
echo "  User: $db_user\n";
echo "  Database: $db_name\n\n";

// Check if using localhost
if ($db_host === 'localhost') {
    echo "  ⚠️  WARNING: Using 'localhost' may cause socket connection issues\n";
    echo "  💡 RECOMMENDATION: Change to '127.0.0.1' in config.php\n\n";
}

echo "Step 3: Testing database connection...\n\n";

// Try different connection methods
$connection_methods = [
    [
        'name' => 'Original config',
        'host' => $db_host,
        'port' => $db_port
    ],
    [
        'name' => 'TCP connection (127.0.0.1)',
        'host' => '127.0.0.1',
        'port' => $db_port ?: 3306
    ],
    [
        'name' => 'Socket connection (localhost)',
        'host' => 'localhost',
        'port' => $db_port
    ]
];

$successful_connection = null;

foreach ($connection_methods as $method) {
    echo "Testing: {$method['name']}\n";
    echo "  DSN: mysql:host={$method['host']}";
    if (!empty($method['port'])) {
        echo ";port={$method['port']}";
    }
    echo "\n";
    
    try {
        $dsn = "mysql:host={$method['host']};dbname=$db_name";
        if (!empty($method['port'])) {
            $dsn .= ";port={$method['port']}";
        }
        
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "  ✓ SUCCESS!\n";
        echo "  MySQL Version: {$result['version']}\n\n";
        
        $successful_connection = $method;
        break;
        
    } catch (PDOException $e) {
        echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
    }
}

if ($successful_connection) {
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        CONNECTION SUCCESSFUL                               ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Working configuration:\n";
    echo "  Host: {$successful_connection['host']}\n";
    if (!empty($successful_connection['port'])) {
        echo "  Port: {$successful_connection['port']}\n";
    }
    echo "\n";
    
    if ($successful_connection['host'] !== $db_host) {
        echo "RECOMMENDATION:\n";
        echo "Update your config.php to use this working configuration:\n\n";
        echo "  \$db_host = '{$successful_connection['host']}';\n";
        if (!empty($successful_connection['port'])) {
            echo "  \$db_port = '{$successful_connection['port']}';\n";
        }
        echo "\n";
    }
    
    echo "You can now run the M-Pesa fix scripts:\n";
    echo "  php fix_mpesa_complete.php\n\n";
    
} else {
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        ALL CONNECTIONS FAILED                              ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "Possible issues:\n";
    echo "1. MySQL/MariaDB is not running\n";
    echo "   Check: systemctl status mariadb  (or mysql)\n";
    echo "   Start: systemctl start mariadb\n\n";
    
    echo "2. Wrong database credentials\n";
    echo "   Check your config.php settings\n";
    echo "   Test: mysql -u $db_user -p$db_pass $db_name\n\n";
    
    echo "3. Database doesn't exist\n";
    echo "   Create: mysql -u root -p -e \"CREATE DATABASE $db_name;\"\n\n";
    
    echo "4. User doesn't have permissions\n";
    echo "   Grant: mysql -u root -p -e \"GRANT ALL ON $db_name.* TO '$db_user'@'localhost';\"\n\n";
    
    echo "5. Firewall blocking connection\n";
    echo "   Check: firewall-cmd --list-all\n\n";
}

echo "=== Diagnostic Complete ===\n";
