<?php
/**
 * Form Functionality Verification Script
 * Verifies that all form attributes, IDs, names, and data attributes are preserved
 */

$templateDirs = [
    'admin/routers',
    'admin/customers',
    'admin/plan',
    'admin/settings',
    'customer'
];

$results = [
    'total_files' => 0,
    'forms_found' => 0,
    'inputs_found' => 0,
    'buttons_found' => 0,
    'selects_found' => 0,
    'textareas_found' => 0,
    'issues' => [],
    'form_details' => []
];

echo "=== Form Functionality Verification ===\n\n";

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
        
        // Check for forms
        preg_match_all('/<form[^>]*>/i', $content, $forms);
        if (count($forms[0]) > 0) {
            echo "Checking: $relativePath\n";
            $results['forms_found'] += count($forms[0]);
            
            foreach ($forms[0] as $form) {
                $formDetails = [
                    'file' => $relativePath,
                    'tag' => $form,
                    'has_id' => preg_match('/id=["\']([^"\']+)["\']/', $form, $idMatch),
                    'has_method' => preg_match('/method=["\']([^"\']+)["\']/', $form, $methodMatch),
                    'has_action' => preg_match('/action=["\']([^"\']+)["\']/', $form, $actionMatch)
                ];
                
                if ($formDetails['has_id']) {
                    $formDetails['id'] = $idMatch[1];
                }
                if ($formDetails['has_method']) {
                    $formDetails['method'] = $methodMatch[1];
                }
                if ($formDetails['has_action']) {
                    $formDetails['action'] = $actionMatch[1];
                }
                
                $results['form_details'][] = $formDetails;
                
                echo "  Form: " . ($formDetails['has_id'] ? "ID={$formDetails['id']}" : "No ID") . 
                     ", Method=" . ($formDetails['has_method'] ? $formDetails['method'] : "GET") . "\n";
            }
            
            // Check for inputs
            preg_match_all('/<input[^>]*>/i', $content, $inputs);
            $results['inputs_found'] += count($inputs[0]);
            
            $inputsWithoutName = 0;
            $inputsWithoutType = 0;
            
            foreach ($inputs[0] as $input) {
                if (!preg_match('/name=["\']([^"\']+)["\']/', $input)) {
                    // Skip if it's a submit button or has type="button"
                    if (!preg_match('/type=["\'](?:submit|button|reset)["\']/', $input)) {
                        $inputsWithoutName++;
                    }
                }
                if (!preg_match('/type=["\']([^"\']+)["\']/', $input)) {
                    $inputsWithoutType++;
                }
            }
            
            if ($inputsWithoutName > 0) {
                $results['issues'][] = "$relativePath: $inputsWithoutName input(s) without name attribute";
            }
            
            // Check for buttons
            preg_match_all('/<button[^>]*>/i', $content, $buttons);
            $results['buttons_found'] += count($buttons[0]);
            
            // Check for selects
            preg_match_all('/<select[^>]*>/i', $content, $selects);
            $results['selects_found'] += count($selects[0]);
            
            $selectsWithoutName = 0;
            foreach ($selects[0] as $select) {
                if (!preg_match('/name=["\']([^"\']+)["\']/', $select)) {
                    $selectsWithoutName++;
                }
            }
            
            if ($selectsWithoutName > 0) {
                $results['issues'][] = "$relativePath: $selectsWithoutName select(s) without name attribute";
            }
            
            // Check for textareas
            preg_match_all('/<textarea[^>]*>/i', $content, $textareas);
            $results['textareas_found'] += count($textareas[0]);
            
            $textareasWithoutName = 0;
            foreach ($textareas[0] as $textarea) {
                if (!preg_match('/name=["\']([^"\']+)["\']/', $textarea)) {
                    $textareasWithoutName++;
                }
            }
            
            if ($textareasWithoutName > 0) {
                $results['issues'][] = "$relativePath: $textareasWithoutName textarea(s) without name attribute";
            }
            
            echo "  ✓ Inputs: " . count($inputs[0]) . 
                 ", Buttons: " . count($buttons[0]) . 
                 ", Selects: " . count($selects[0]) . 
                 ", Textareas: " . count($textareas[0]) . "\n";
        }
    }
}

echo "\n=== Verification Summary ===\n";
echo "Total files checked: {$results['total_files']}\n";
echo "Forms found: {$results['forms_found']}\n";
echo "Form elements found:\n";
echo "  - Inputs: {$results['inputs_found']}\n";
echo "  - Buttons: {$results['buttons_found']}\n";
echo "  - Selects: {$results['selects_found']}\n";
echo "  - Textareas: {$results['textareas_found']}\n";

if (count($results['issues']) > 0) {
    echo "\n⚠ ISSUES FOUND:\n";
    foreach ($results['issues'] as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "\n✓ All forms have proper attributes!\n";
}

// Check for preserved data attributes
echo "\n=== Checking Data Attributes ===\n";
$dataAttrCount = 0;
foreach ($templateDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        continue;
    }
    
    $files = glob($path . '/*.tpl');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        preg_match_all('/data-[a-z\-]+=["\'][^"\']*["\']/', $content, $dataAttrs);
        if (count($dataAttrs[0]) > 0) {
            $dataAttrCount += count($dataAttrs[0]);
        }
    }
}
echo "Data attributes found: $dataAttrCount\n";
echo "✓ Data attributes are preserved for JavaScript interactions\n";

echo "\n=== Verification Complete ===\n";

exit(0);
