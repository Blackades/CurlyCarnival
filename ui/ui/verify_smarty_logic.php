<?php
/**
 * Smarty Template Logic Verification Script
 * Verifies that all Smarty variables, conditionals, and loops are syntactically correct
 */

// Define template directories to check
$templateDirs = [
    'widget',
    'admin',
    'admin/routers',
    'admin/customers',
    'admin/plan',
    'admin/settings',
    'customer'
];

$results = [
    'total_files' => 0,
    'checked_files' => 0,
    'errors' => [],
    'warnings' => [],
    'smarty_elements' => [
        'variables' => 0,
        'conditionals' => 0,
        'loops' => 0,
        'functions' => 0,
        'includes' => 0
    ]
];

echo "=== Smarty Template Logic Verification ===\n\n";

foreach ($templateDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    
    $files = glob($path . '/*.tpl');
    foreach ($files as $file) {
        $results['total_files']++;
        $filename = basename($file);
        $relativePath = $dir . '/' . $filename;
        
        echo "Checking: $relativePath\n";
        
        $content = file_get_contents($file);
        $results['checked_files']++;
        
        // Check for Smarty variables
        preg_match_all('/\{\$[a-zA-Z_][a-zA-Z0-9_\[\]\'\"\-\>]*\}/', $content, $variables);
        $results['smarty_elements']['variables'] += count($variables[0]);
        
        // Check for conditionals
        preg_match_all('/\{if\s+/', $content, $ifs);
        preg_match_all('/\{\/if\}/', $content, $endifs);
        $results['smarty_elements']['conditionals'] += count($ifs[0]);
        
        // Check for loops
        preg_match_all('/\{foreach\s+/', $content, $foreachs);
        preg_match_all('/\{\/foreach\}/', $content, $endforeachs);
        $results['smarty_elements']['loops'] += count($foreachs[0]);
        
        // Check for functions
        preg_match_all('/\{function\s+/', $content, $functions);
        $results['smarty_elements']['functions'] += count($functions[0]);
        
        // Check for includes
        preg_match_all('/\{include\s+/', $content, $includes);
        $results['smarty_elements']['includes'] += count($includes[0]);
        
        // Verify matching tags
        if (count($ifs[0]) !== count($endifs[0])) {
            $results['errors'][] = "$relativePath: Mismatched {if} tags - " . count($ifs[0]) . " opening, " . count($endifs[0]) . " closing";
        }
        
        if (count($foreachs[0]) !== count($endforeachs[0])) {
            $results['errors'][] = "$relativePath: Mismatched {foreach} tags - " . count($foreachs[0]) . " opening, " . count($endforeachs[0]) . " closing";
        }
        
        // Check for common syntax errors
        if (preg_match('/\{[^}]*\{/', $content)) {
            $results['warnings'][] = "$relativePath: Possible nested Smarty tags without proper escaping";
        }
        
        // Check for unclosed tags
        if (preg_match('/\{[^}]*$/', $content)) {
            $results['errors'][] = "$relativePath: Possible unclosed Smarty tag at end of file";
        }
        
        echo "  ✓ Variables: " . count($variables[0]) . ", Conditionals: " . count($ifs[0]) . ", Loops: " . count($foreachs[0]) . "\n";
    }
}

echo "\n=== Verification Summary ===\n";
echo "Total files checked: {$results['checked_files']}/{$results['total_files']}\n";
echo "\nSmarty Elements Found:\n";
echo "  - Variables: {$results['smarty_elements']['variables']}\n";
echo "  - Conditionals: {$results['smarty_elements']['conditionals']}\n";
echo "  - Loops: {$results['smarty_elements']['loops']}\n";
echo "  - Functions: {$results['smarty_elements']['functions']}\n";
echo "  - Includes: {$results['smarty_elements']['includes']}\n";

if (count($results['errors']) > 0) {
    echo "\n❌ ERRORS FOUND:\n";
    foreach ($results['errors'] as $error) {
        echo "  - $error\n";
    }
} else {
    echo "\n✓ No syntax errors found!\n";
}

if (count($results['warnings']) > 0) {
    echo "\n⚠ WARNINGS:\n";
    foreach ($results['warnings'] as $warning) {
        echo "  - $warning\n";
    }
}

echo "\n=== Verification Complete ===\n";

// Return exit code based on errors
exit(count($results['errors']) > 0 ? 1 : 0);
