# Design Document

## Overview

The voucher activation page error occurs because the application expects a `pages/` directory to exist with customizable HTML template files, but this directory is not automatically created during installation or initialization. The application has a `pages_template/` directory with default templates, but these are not copied to the working `pages/` directory.

The solution involves implementing an automatic initialization mechanism that ensures the `pages/` directory exists and is populated with default templates before any page rendering occurs. This will be implemented in the initialization phase of the application to prevent errors across all customer-facing pages that rely on these templates.

## Architecture

### Current Flow
1. Application initializes via `init.php`
2. `$PAGES_PATH` variable is set to `pages/` directory
3. Controller loads (e.g., `voucher.php`)
4. Template attempts to include file from `$PAGES_PATH`
5. **ERROR**: File/directory doesn't exist, causing Smarty template exception

### Proposed Flow
1. Application initializes via `init.php`
2. `$PAGES_PATH` variable is set to `pages/` directory
3. **NEW**: Pages directory initialization check runs
4. **NEW**: If directory missing, create and populate from `pages_template/`
5. Controller loads normally
6. Template successfully includes file from `$PAGES_PATH`

## Components and Interfaces

### 1. Pages Initialization Module

**Location**: `system/autoload/Pages.php` (new file)

**Purpose**: Centralized logic for ensuring pages directory exists and is properly initialized

**Methods**:
- `Pages::initialize()` - Main initialization method
  - Checks if `pages/` directory exists
  - Creates directory if missing
  - Copies all template files from `pages_template/` to `pages/`
  - Returns boolean success status

- `Pages::ensureTemplateExists($templateName)` - On-demand template verification
  - Checks if specific template file exists in `pages/`
  - Copies from `pages_template/` if missing
  - Returns boolean indicating if template is available

- `Pages::copyTemplateFile($templateName)` - Copy individual template
  - Copies single template file from source to destination
  - Handles subdirectories (e.g., `vouchers/`)
  - Returns boolean success status

### 2. Initialization Hook in init.php

**Location**: `init.php` (modification)

**Implementation**: Add initialization call after `$PAGES_PATH` is defined

```php
// After line 57 where $PAGES_PATH is defined
$PAGES_PATH = $root_path . File::pathFixer('pages');

// Add initialization
if (!file_exists($PAGES_PATH)) {
    Pages::initialize($PAGES_PATH, $root_path . File::pathFixer('pages_template'));
}
```

### 3. Voucher Controller Enhancement

**Location**: `system/controllers/voucher.php` (modification)

**Implementation**: Add template verification before rendering activation page

```php
case 'activation':
    // Ensure Order_Voucher template exists
    Pages::ensureTemplateExists('Order_Voucher.html');
    
    run_hook('view_activate_voucher'); #HOOK
    $ui->assign('code', alphanumeric(_get('code'), "-_.,"));
    $ui->display('customer/activation.tpl');
    break;
```

## Data Models

No database changes required. This is a file system operation only.

### File System Structure

**Before Fix**:
```
/
├── pages_template/
│   ├── Order_Voucher.html
│   ├── Voucher.html
│   ├── Email.html
│   └── vouchers/
│       └── (template files)
└── (pages/ directory missing)
```

**After Fix**:
```
/
├── pages_template/
│   ├── Order_Voucher.html
│   ├── Voucher.html
│   ├── Email.html
│   └── vouchers/
│       └── (template files)
└── pages/
    ├── Order_Voucher.html
    ├── Voucher.html
    ├── Email.html
    └── vouchers/
        └── (template files)
```

## Error Handling

### Scenario 1: Pages Directory Cannot Be Created
- **Detection**: `mkdir()` returns false
- **Response**: Log error and throw exception with clear message
- **User Impact**: Display maintenance page with instructions for manual directory creation

### Scenario 2: Template Files Cannot Be Copied
- **Detection**: `copy()` returns false for individual files
- **Response**: Log warning but continue with other files
- **User Impact**: Specific pages may show empty content but no crash

### Scenario 3: Source Template Directory Missing
- **Detection**: `pages_template/` doesn't exist
- **Response**: Log critical error and attempt to download from GitHub repository
- **User Impact**: Temporary delay during first access, then normal operation

### Scenario 4: Permission Issues
- **Detection**: `is_writable()` returns false
- **Response**: Log error with specific permission requirements
- **User Impact**: Display error message with chmod instructions

## Testing Strategy

### Unit Tests (Optional)
- Test `Pages::initialize()` with various directory states
- Test `Pages::ensureTemplateExists()` with missing/existing templates
- Test `Pages::copyTemplateFile()` with valid/invalid paths

### Integration Tests
1. **Fresh Installation Test**
   - Delete `pages/` directory
   - Access voucher activation page
   - Verify page loads without error
   - Verify `pages/` directory is created
   - Verify template files are copied

2. **Missing Template Test**
   - Delete specific template file from `pages/`
   - Access page requiring that template
   - Verify template is automatically restored
   - Verify page renders correctly

3. **Permission Test**
   - Set `pages/` directory to read-only
   - Access voucher activation page
   - Verify appropriate error message is displayed
   - Verify error is logged

### Manual Testing
1. Navigate to `/?_route=voucher/activation` as customer
2. Verify page loads without "Internal Error"
3. Verify "Order Voucher" section displays correctly
4. Verify voucher activation form is functional
5. Check that `pages/` directory exists after first access
6. Verify all template files are present in `pages/`

## Implementation Notes

### Existing Pattern Analysis
The application already has a similar pattern in `system/controllers/pages.php`:
- Lines 40-46 show template copying logic
- Uses `copy()` function with fallback to `touch()`
- Creates subdirectories as needed (vouchers/)

Our implementation will follow this established pattern but move it to initialization phase for proactive handling rather than reactive.

### Performance Considerations
- Directory check is fast (single `file_exists()` call)
- Copying only happens once per installation
- No performance impact after initial setup
- Consider adding flag file to skip check after successful initialization

### Backward Compatibility
- No breaking changes to existing functionality
- Existing installations with `pages/` directory unaffected
- New installations will work immediately
- Upgrade path is automatic on first page load

## Security Considerations

1. **Path Traversal Prevention**: Use `str_replace(".", "", $templateName)` to prevent directory traversal attacks
2. **File Type Validation**: Only copy `.html` files from template directory
3. **Permission Verification**: Ensure created directories have appropriate permissions (0755)
4. **Logging**: Log all directory creation and file copy operations for audit trail
