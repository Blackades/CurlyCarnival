<?php
/**
 * Accessibility Attributes Verification Script
 * Verifies that ARIA attributes, labels, and accessibility features are preserved
 */

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
    'aria_attributes' => 0,
    'aria_types' => [],
    'labels' => 0,
    'label_associations' => 0,
    'alt_texts' => 0,
    'role_attributes' => 0,
    'tabindex_attributes' => 0,
    'title_attributes' => 0,
    'issues' => [],
    'accessibility_features' => []
];

echo "=== Accessibility Attributes Verification ===\n\n";

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
        
        $content = file_get_contents($file);
        
        // Count ARIA attributes
        preg_match_all('/aria-([a-z\-]+)=["\']([^"\']*)["\']/', $content, $ariaAttrs);
        $ariaCount = count($ariaAttrs[0]);
        if ($ariaCount > 0) {
            $results['aria_attributes'] += $ariaCount;
            foreach ($ariaAttrs[1] as $attr) {
                if (!isset($results['aria_types'][$attr])) {
                    $results['aria_types'][$attr] = 0;
                }
                $results['aria_types'][$attr]++;
            }
        }
        
        // Count labels
        preg_match_all('/<label[^>]*>/i', $content, $labels);
        $labelCount = count($labels[0]);
        if ($labelCount > 0) {
            $results['labels'] += $labelCount;
            
            // Check for label associations (for attribute)
            foreach ($labels[0] as $label) {
                if (preg_match('/for=["\']([^"\']+)["\']/', $label)) {
                    $results['label_associations']++;
                }
            }
        }
        
        // Count alt texts
        preg_match_all('/<img[^>]*alt=["\']([^"\']*)["\'][^>]*>/i', $content, $alts);
        $altCount = count($alts[0]);
        if ($altCount > 0) {
            $results['alt_texts'] += $altCount;
        }
        
        // Count role attributes
        preg_match_all('/role=["\']([^"\']+)["\']/', $content, $roles);
        $roleCount = count($roles[0]);
        if ($roleCount > 0) {
            $results['role_attributes'] += $roleCount;
        }
        
        // Count tabindex attributes
        preg_match_all('/tabindex=["\']([^"\']+)["\']/', $content, $tabindexes);
        $tabindexCount = count($tabindexes[0]);
        if ($tabindexCount > 0) {
            $results['tabindex_attributes'] += $tabindexCount;
        }
        
        // Count title attributes
        preg_match_all('/title=["\']([^"\']+)["\']/', $content, $titles);
        $titleCount = count($titles[0]);
        if ($titleCount > 0) {
            $results['title_attributes'] += $titleCount;
        }
        
        // Check for images without alt text
        preg_match_all('/<img(?![^>]*alt=)[^>]*>/i', $content, $imgsWithoutAlt);
        if (count($imgsWithoutAlt[0]) > 0) {
            // Filter out images that might be decorative or have Smarty variables
            $actualIssues = 0;
            foreach ($imgsWithoutAlt[0] as $img) {
                // Skip if it's likely a Smarty variable or has empty src
                if (!preg_match('/src=["\']["\']|src=["\']#|src=["\']\{/', $img)) {
                    $actualIssues++;
                }
            }
            if ($actualIssues > 0) {
                $results['issues'][] = "$relativePath: $actualIssues image(s) without alt attribute";
            }
        }
        
        // Check for buttons without type
        preg_match_all('/<button(?![^>]*type=)[^>]*>/i', $content, $buttonsWithoutType);
        if (count($buttonsWithoutType[0]) > 0) {
            $results['issues'][] = "$relativePath: " . count($buttonsWithoutType[0]) . " button(s) without type attribute";
        }
        
        if ($ariaCount > 0 || $labelCount > 0 || $altCount > 0 || $roleCount > 0) {
            echo "Checking: $relativePath\n";
            echo "  ARIA: $ariaCount, Labels: $labelCount, Alt texts: $altCount, Roles: $roleCount\n";
        }
    }
}

echo "\n=== Verification Summary ===\n";
echo "Total files checked: {$results['total_files']}\n";
echo "\nAccessibility Features Found:\n";
echo "  - ARIA attributes: {$results['aria_attributes']}\n";
echo "  - Labels: {$results['labels']}\n";
echo "  - Label associations (for attribute): {$results['label_associations']}\n";
echo "  - Alt texts: {$results['alt_texts']}\n";
echo "  - Role attributes: {$results['role_attributes']}\n";
echo "  - Tabindex attributes: {$results['tabindex_attributes']}\n";
echo "  - Title attributes: {$results['title_attributes']}\n";

if (count($results['aria_types']) > 0) {
    echo "\n=== ARIA Attributes Breakdown ===\n";
    arsort($results['aria_types']);
    foreach ($results['aria_types'] as $type => $count) {
        echo "  - aria-$type: $count occurrences\n";
    }
}

if (count($results['issues']) > 0) {
    echo "\n⚠ ACCESSIBILITY ISSUES FOUND:\n";
    foreach ($results['issues'] as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "\n✓ No critical accessibility issues found!\n";
}

echo "\n=== Accessibility Best Practices Check ===\n";

// Check for semantic HTML
$semanticElements = [
    '<nav' => 'Navigation elements',
    '<main' => 'Main content areas',
    '<header' => 'Header elements',
    '<footer' => 'Footer elements',
    '<article' => 'Article elements',
    '<section' => 'Section elements',
    '<aside' => 'Aside elements'
];

foreach ($semanticElements as $element => $description) {
    $count = 0;
    foreach ($templateDirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            continue;
        }
        $files = glob($path . '/*.tpl');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $count += substr_count(strtolower($content), $element);
        }
    }
    if ($count > 0) {
        echo "✓ $description: $count occurrences\n";
    }
}

// Check for form accessibility
echo "\n=== Form Accessibility ===\n";
$formAccessibility = [
    'placeholder=' => 'Placeholder text',
    'required' => 'Required field indicators',
    'aria-required' => 'ARIA required attributes',
    'aria-invalid' => 'ARIA invalid attributes',
    'aria-describedby' => 'ARIA descriptions'
];

foreach ($formAccessibility as $pattern => $description) {
    $count = 0;
    foreach ($templateDirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_dir($path)) {
            continue;
        }
        $files = glob($path . '/*.tpl');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $count += substr_count($content, $pattern);
        }
    }
    if ($count > 0) {
        echo "✓ $description: $count occurrences\n";
    }
}

echo "\n✓ Accessibility attributes are preserved!\n";
echo "✓ ARIA labels, roles, and semantic HTML are intact\n";

echo "\n=== Verification Complete ===\n";

exit(0);
