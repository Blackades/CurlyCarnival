<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  Pages initialization and template management
 **/

class Pages
{
    /**
     * Initialize pages directory and copy all templates from pages_template
     * 
     * @param string $pagesPath Path to pages directory
     * @param string $templatePath Path to pages_template directory
     * @return bool Success status
     */
    public static function initialize($pagesPath = null, $templatePath = null)
    {
        global $root_path;
        
        // Set default paths if not provided
        if ($pagesPath === null) {
            $pagesPath = $root_path . File::pathFixer('pages');
        }
        if ($templatePath === null) {
            $templatePath = $root_path . File::pathFixer('pages_template');
        }
        
        // Check if pages directory already exists
        if (file_exists($pagesPath)) {
            _log("Pages directory already exists at: $pagesPath", 'Pages', 0);
            return true;
        }
        
        // Check if template directory exists
        if (!file_exists($templatePath)) {
            _log("Pages template directory not found at: $templatePath", 'Pages', 0);
            return false;
        }
        
        // Create pages directory
        if (!mkdir($pagesPath, 0755, true)) {
            _log("Failed to create pages directory at: $pagesPath", 'Pages', 0);
            return false;
        }
        
        _log("Created pages directory at: $pagesPath", 'Pages', 0);
        
        // Copy all template files
        $success = self::copyAllTemplates($pagesPath, $templatePath);
        
        if ($success) {
            _log("Successfully initialized pages directory with all templates", 'Pages', 0);
        } else {
            _log("Pages directory created but some templates failed to copy", 'Pages', 0);
        }
        
        return $success;
    }
    
    /**
     * Copy all template files from pages_template to pages directory
     * 
     * @param string $pagesPath Path to pages directory
     * @param string $templatePath Path to pages_template directory
     * @return bool Success status
     */
    private static function copyAllTemplates($pagesPath, $templatePath)
    {
        $allSuccess = true;
        
        // Get all files from template directory
        $files = scandir($templatePath);
        
        foreach ($files as $file) {
            // Skip . and .. and .htaccess
            if ($file === '.' || $file === '..' || $file === '.htaccess') {
                continue;
            }
            
            $sourcePath = $templatePath . DIRECTORY_SEPARATOR . $file;
            $destPath = $pagesPath . DIRECTORY_SEPARATOR . $file;
            
            // Handle directories (like vouchers/)
            if (is_dir($sourcePath)) {
                if (!file_exists($destPath)) {
                    if (!mkdir($destPath, 0755, true)) {
                        _log("Failed to create subdirectory: $destPath", 'Pages', 0);
                        $allSuccess = false;
                        continue;
                    }
                    _log("Created subdirectory: $destPath", 'Pages', 0);
                }
                
                // Recursively copy subdirectory contents
                $subFiles = scandir($sourcePath);
                foreach ($subFiles as $subFile) {
                    if ($subFile === '.' || $subFile === '..') {
                        continue;
                    }
                    
                    $subSource = $sourcePath . DIRECTORY_SEPARATOR . $subFile;
                    $subDest = $destPath . DIRECTORY_SEPARATOR . $subFile;
                    
                    if (is_file($subSource)) {
                        if (!copy($subSource, $subDest)) {
                            _log("Failed to copy template file: $subFile from subdirectory $file", 'Pages', 0);
                            $allSuccess = false;
                        } else {
                            _log("Copied template file: $subFile to subdirectory $file", 'Pages', 0);
                        }
                    }
                }
            } 
            // Handle regular files
            else if (is_file($sourcePath)) {
                if (!copy($sourcePath, $destPath)) {
                    _log("Failed to copy template file: $file", 'Pages', 0);
                    $allSuccess = false;
                } else {
                    _log("Copied template file: $file", 'Pages', 0);
                }
            }
        }
        
        return $allSuccess;
    }
    
    /**
     * Ensure a specific template file exists, copy from template if missing
     * 
     * @param string $templateName Name of the template file (e.g., 'Order_Voucher.html')
     * @param string $pagesPath Path to pages directory (optional)
     * @param string $templatePath Path to pages_template directory (optional)
     * @return bool True if template exists or was successfully copied
     */
    public static function ensureTemplateExists($templateName, $pagesPath = null, $templatePath = null)
    {
        global $root_path;
        
        // Sanitize template name to prevent directory traversal
        $templateName = str_replace("..", "", $templateName);
        $templateName = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $templateName);
        
        // Set default paths if not provided
        if ($pagesPath === null) {
            $pagesPath = $root_path . File::pathFixer('pages');
        }
        if ($templatePath === null) {
            $templatePath = $root_path . File::pathFixer('pages_template');
        }
        
        $destFile = $pagesPath . DIRECTORY_SEPARATOR . $templateName;
        
        // Check if template already exists
        if (file_exists($destFile)) {
            return true;
        }
        
        _log("Template file not found, attempting to copy: $templateName", 'Pages', 0);
        
        // Ensure pages directory exists
        if (!file_exists($pagesPath)) {
            if (!mkdir($pagesPath, 0755, true)) {
                _log("Failed to create pages directory for template: $templateName", 'Pages', 0);
                return false;
            }
            _log("Created pages directory for template: $templateName", 'Pages', 0);
        }
        
        // Copy the template file
        return self::copyTemplateFile($templateName, $pagesPath, $templatePath);
    }
    
    /**
     * Copy a single template file from pages_template to pages directory
     * 
     * @param string $templateName Name of the template file
     * @param string $pagesPath Path to pages directory
     * @param string $templatePath Path to pages_template directory
     * @return bool Success status
     */
    public static function copyTemplateFile($templateName, $pagesPath = null, $templatePath = null)
    {
        global $root_path;
        
        // Sanitize template name to prevent directory traversal
        $templateName = str_replace("..", "", $templateName);
        
        // Set default paths if not provided
        if ($pagesPath === null) {
            $pagesPath = $root_path . File::pathFixer('pages');
        }
        if ($templatePath === null) {
            $templatePath = $root_path . File::pathFixer('pages_template');
        }
        
        $sourceFile = $templatePath . DIRECTORY_SEPARATOR . $templateName;
        $destFile = $pagesPath . DIRECTORY_SEPARATOR . $templateName;
        
        // Check if source template exists
        if (!file_exists($sourceFile)) {
            _log("Source template file not found: $templateName", 'Pages', 0);
            
            // Try to download from GitHub as fallback
            try {
                $githubUrl = 'https://raw.githubusercontent.com/hotspotbilling/phpnuxbill/master/pages_template/' . $templateName;
                $content = Http::getData($githubUrl);
                
                if ($content !== false && !empty($content)) {
                    // Ensure subdirectory exists if template is in a subdirectory
                    $destDir = dirname($destFile);
                    if (!file_exists($destDir)) {
                        if (!mkdir($destDir, 0755, true)) {
                            _log("Failed to create subdirectory for template: $templateName", 'Pages', 0);
                            return false;
                        }
                    }
                    
                    if (file_put_contents($destFile, $content) !== false) {
                        _log("Downloaded template from GitHub: $templateName", 'Pages', 0);
                        return true;
                    }
                }
            } catch (Exception $e) {
                _log("Failed to download template from GitHub: $templateName - " . $e->getMessage(), 'Pages', 0);
            }
            
            return false;
        }
        
        // Ensure subdirectory exists if template is in a subdirectory
        $destDir = dirname($destFile);
        if (!file_exists($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                _log("Failed to create subdirectory for template: $templateName", 'Pages', 0);
                return false;
            }
            _log("Created subdirectory for template: $templateName", 'Pages', 0);
        }
        
        // Copy the file
        if (!copy($sourceFile, $destFile)) {
            _log("Failed to copy template file: $templateName", 'Pages', 0);
            return false;
        }
        
        _log("Successfully copied template file: $templateName", 'Pages', 0);
        return true;
    }
}
