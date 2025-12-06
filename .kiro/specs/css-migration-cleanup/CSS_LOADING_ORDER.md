# CSS Loading Order Documentation

## Standard CSS Loading Order

This document defines the standard CSS loading order for PHPNuxBill templates to ensure proper styling and prevent conflicts.

## Loading Sequence

### 1. Icon Fonts (First)
Icon fonts must load first as they have no dependencies and are used throughout the application.

```html
<!-- Icon Fonts -->
<link rel="stylesheet" href="{$app_url}/ui/ui/fonts/ionicons/css/ionicons.min.css">
<link rel="stylesheet" href="{$app_url}/ui/ui/fonts/font-awesome/css/font-awesome.min.css">
```

**Why First?**
- No dependencies on other CSS
- Required by many components
- Small file size, loads quickly

### 2. Plugin CSS (Second)
Third-party plugin CSS files load second to establish their base styles.

#### Customer Templates:
```html
<!-- Plugin CSS -->
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
```

#### Admin Templates:
```html
<!-- Plugin CSS -->
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/select2.min.css" />
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/select2-bootstrap.min.css" />
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/sweetalert2.min.css" />
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/plugins/pace.css" />
<link rel="stylesheet" href="{$app_url}/ui/ui/summernote/summernote.min.css" />
```

**Why Second?**
- Establishes plugin base styles
- Can be overridden by application CSS if needed
- Maintains plugin functionality

### 3. Modern CSS System (Third)
The main application CSS file loads third to apply core application styles.

```html
<!-- Modern CSS System -->
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/phpnuxbill-modern.css?v=2025.12.07.001" />
```

**Why Third?**
- Contains all application-specific styles
- Can override plugin styles if needed
- Imports all modular CSS files in correct order
- Uses version parameter for cache busting

**Module Import Order (inside phpnuxbill-modern.css):**
1. Base modules (variables, reset, typography)
2. Layout modules (grid, containers, header, sidebar, footer)
3. Component modules (buttons, forms, tables, cards, modals, alerts, badges, navigation)
4. Utility modules (spacing, colors, display)
5. Responsive modules (mobile, tablet, desktop)

### 4. Accessibility CSS (Fourth)
Accessibility enhancements load last to ensure they take precedence.

```html
<!-- Accessibility -->
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/accessibility/skip-links.css" />
<link rel="stylesheet" href="{$app_url}/ui/ui/styles/accessibility/focus-indicators.css" />
```

**Why Fourth?**
- Must override other styles for accessibility
- Ensures focus indicators are visible
- Skip links must be positioned correctly
- Critical for keyboard navigation

## Version Parameter Strategy

**Format:** `?v=YYYY.MM.DD.NNN`

**Example:** `phpnuxbill-modern.css?v=2025.12.07.001`

**Components:**
- `YYYY` - Year (4 digits)
- `MM` - Month (2 digits)
- `DD` - Day (2 digits)
- `NNN` - Revision number (3 digits: 001, 002, etc.)

**When to Update:**
- Increment revision number (NNN) for minor CSS tweaks
- Change date for major CSS updates
- Use same version across all templates for consistency

## Removed Legacy CSS Files

The following CSS files have been removed and should NOT be referenced:

❌ `bootstrap.min.css` - Replaced by custom grid and component system
❌ `modern-AdminLTE.min.css` - Replaced by modular layout system
❌ `phpnuxbill.customer.css` - Replaced by phpnuxbill-modern.css
❌ `phpnux.omer.css` - Replaced by phpnuxbill-modern.css
❌ `dashboard-fix.css` - Integrated into modern CSS modules
❌ `phpnuxbill.css` - Replaced by phpnuxbill-modern.css
❌ `7.css` - Replaced by phpnuxbill-modern.css

## Template-Specific Differences

### Customer Templates (customer/header.tpl)
- Minimal plugin CSS (only SweetAlert2)
- No admin-specific plugins (Select2, Summernote, Pace)
- Same core structure as admin

### Admin Templates (admin/header.tpl)
- Full plugin CSS suite
- Includes Select2 for enhanced dropdowns
- Includes Summernote for rich text editing
- Includes Pace for loading indicators

## Verification Checklist

✅ Icon fonts load first
✅ Plugin CSS loads second
✅ phpnuxbill-modern.css loads third
✅ Accessibility CSS loads fourth
✅ Version parameter is current (2025.12.07.001)
✅ No legacy CSS files referenced
✅ No 404 errors in browser console
✅ All styles render correctly

## Troubleshooting

### Problem: Styles not applying
**Solution:**
1. Verify CSS loading order matches this document
2. Check browser Network tab for 404 errors
3. Clear browser cache
4. Verify version parameter is current

### Problem: Plugin styles broken
**Solution:**
1. Verify plugin CSS loads before phpnuxbill-modern.css
2. Check that plugin CSS files exist
3. Verify plugin JavaScript is loaded

### Problem: Accessibility features not working
**Solution:**
1. Verify accessibility CSS loads last
2. Check that skip-links.css and focus-indicators.css exist
3. Test with keyboard navigation (Tab key)

## Maintenance

When adding new CSS files:
1. Determine appropriate loading position
2. Add with descriptive comment
3. Update this documentation
4. Test across all templates
5. Verify no conflicts with existing styles

When updating existing CSS:
1. Increment version parameter
2. Test in both customer and admin templates
3. Clear browser cache
4. Verify no regressions

## References

- Design Document: `.kiro/specs/css-migration-cleanup/design.md`
- Requirements Document: `.kiro/specs/css-migration-cleanup/requirements.md`
- Tasks Document: `.kiro/specs/css-migration-cleanup/tasks.md`
