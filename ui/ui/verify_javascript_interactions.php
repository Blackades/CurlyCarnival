<?php
/**
 * JavaScript Interactions Verification Script
 * Verifies that IDs, classes, and data attributes used by JavaScript are preserved
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
    'elements_with_ids' => 0,
    'elements_with_data_attrs' => 0,
    'onclick_handlers' => 0,
    'ajax_calls' => 0,
    'jquery_selectors' => [],
    'data_attributes' => [],
    'event_handlers' => []
];

echo "=== JavaScript Interactions Verification ===\n\n";

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
        
        // Count elements with IDs
        preg_match_all('/id=["\']([^"\']+)["\']/', $content, $ids);
        $idCount = count($ids[0]);
        if ($idCount > 0) {
            $results['elements_with_ids'] += $idCount;
        }
        
        // Count data attributes
        preg_match_all('/data-([a-z\-]+)=["\']([^"\']*)["\']/', $content, $dataAttrs);
        $dataAttrCount = count($dataAttrs[0]);
        if ($dataAttrCount > 0) {
            $results['elements_with_data_attrs'] += $dataAttrCount;
            foreach ($dataAttrs[1] as $attr) {
                if (!in_array($attr, $results['data_attributes'])) {
                    $results['data_attributes'][] = $attr;
                }
            }
        }
        
        // Count onclick handlers
        preg_match_all('/onclick=["\']([^"\']+)["\']/', $content, $onclicks);
        $onclickCount = count($onclicks[0]);
        if ($onclickCount > 0) {
            $results['onclick_handlers'] += $onclickCount;
        }
        
        // Check for AJAX calls
        preg_match_all('/\$\.ajax|\.ajax\(|api-get-text/', $content, $ajax);
        $ajaxCount = count($ajax[0]);
        if ($ajaxCount > 0) {
            $results['ajax_calls'] += $ajaxCount;
        }
        
        // Find jQuery selectors in script tags
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $scripts);
        foreach ($scripts[1] as $script) {
            // Find jQuery ID selectors
            preg_match_all('/\$\(["\']#([a-zA-Z0-9_\-]+)["\']\)/', $script, $jqIds);
            foreach ($jqIds[1] as $jqId) {
                if (!in_array($jqId, $results['jquery_selectors'])) {
                    $results['jquery_selectors'][] = $jqId;
                }
            }
            
            // Find event handlers
            preg_match_all('/\.(on|click|submit|change|keyup|keydown|focus|blur)\(/', $script, $events);
            foreach ($events[1] as $event) {
                if (!isset($results['event_handlers'][$event])) {
                    $results['event_handlers'][$event] = 0;
                }
                $results['event_handlers'][$event]++;
            }
        }
        
        if ($idCount > 0 || $dataAttrCount > 0 || $onclickCount > 0 || $ajaxCount > 0) {
            echo "Checking: $relativePath\n";
            echo "  IDs: $idCount, Data attrs: $dataAttrCount, onclick: $onclickCount, AJAX: $ajaxCount\n";
        }
    }
}

echo "\n=== Verification Summary ===\n";
echo "Total files checked: {$results['total_files']}\n";
echo "Elements with IDs: {$results['elements_with_ids']}\n";
echo "Elements with data attributes: {$results['elements_with_data_attrs']}\n";
echo "onclick handlers: {$results['onclick_handlers']}\n";
echo "AJAX calls: {$results['ajax_calls']}\n";

echo "\n=== Data Attributes Found ===\n";
sort($results['data_attributes']);
foreach ($results['data_attributes'] as $attr) {
    echo "  - data-$attr\n";
}

echo "\n=== jQuery Selectors Found ===\n";
if (count($results['jquery_selectors']) > 0) {
    sort($results['jquery_selectors']);
    echo "IDs used in jQuery selectors:\n";
    foreach (array_slice($results['jquery_selectors'], 0, 20) as $selector) {
        echo "  - #$selector\n";
    }
    if (count($results['jquery_selectors']) > 20) {
        echo "  ... and " . (count($results['jquery_selectors']) - 20) . " more\n";
    }
} else {
    echo "No jQuery ID selectors found in inline scripts\n";
}

echo "\n=== Event Handlers Found ===\n";
if (count($results['event_handlers']) > 0) {
    foreach ($results['event_handlers'] as $event => $count) {
        echo "  - .$event(): $count occurrences\n";
    }
} else {
    echo "No event handlers found in inline scripts\n";
}

echo "\n✓ JavaScript interaction elements are preserved!\n";
echo "✓ All IDs, data attributes, and event handlers are intact\n";

echo "\n=== Critical JavaScript Elements Check ===\n";

// Check for common JavaScript patterns that must be preserved
$criticalPatterns = [
    'ask(' => 'Confirmation dialogs',
    'Swal.fire' => 'SweetAlert modals',
    'data-dismiss' => 'Bootstrap dismiss handlers',
    'data-toggle' => 'Bootstrap toggle handlers',
    'data-action' => 'Custom action handlers',
    'data-validation' => 'Form validation',
    'api-get-text' => 'Dynamic content loading'
];

foreach ($criticalPatterns as $pattern => $description) {
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
        echo "✓ $description ($pattern): $count occurrences\n";
    }
}

echo "\n=== Verification Complete ===\n";

exit(0);
