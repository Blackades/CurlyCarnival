<?php

/**
 * Test Config Loading
 * 
 * Quick test to verify config.php can be found and loaded
 */

echo "=== Config Loading Test ===\n\n";

// Show current directory
echo "Current directory: " . getcwd() . "\n";
echo "Script location: " . __DIR__ . "\n\n";

// Try to find config.php
$config_path = dirname(__DIR__) . '/config.php';
echo "Looking for config at: $config_path\n";

if (file_exists($config_path)) {
    echo "✓ Found config.php\n\n";
    
    // Load it
    require_once $config_path;
    
    echo "Database configuration:\n";
    echo "  Host: " . (isset($db_host) ? $db_host : 'NOT SET') . "\n";
    echo "  Port: " . (isset($db_port) && !empty($db_port) ? $db_port : '(default)') . "\n";
    echo "  User: " . (isset($db_user) ? $db_user : 'NOT SET') . "\n";
    echo "  Database: " . (isset($db_name) ? $db_name : 'NOT SET') . "\n\n";
    
    if ($db_host === 'localhost') {
        echo "⚠️  WARNING: Using 'localhost' may cause socket issues\n";
        echo "💡 RECOMMENDATION: Change to '127.0.0.1' in config.php\n\n";
    }
    
    echo "✓ Config loaded successfully\n";
    echo "\nYou can now run:\n";
    echo "  php check_db_connection.php\n";
    echo "  php fix_mpesa_complete.php\n";
    
} else {
    echo "❌ config.php NOT FOUND\n\n";
    echo "Expected location: $config_path\n\n";
    echo "Please create config.php:\n";
    echo "  cd " . dirname(__DIR__) . "\n";
    echo "  cp config.sample.php config.php\n";
    echo "  nano config.php  # Edit database settings\n";
    exit(1);
}
