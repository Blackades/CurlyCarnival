# ARIA Implementation Guide for PHPNuxBill

## Overview

This document describes the ARIA (Accessible Rich Internet Applications) attributes implementation across the PHPNuxBill application to enhance accessibility for users with assistive technologies.

## Implementation Status

### ✅ Completed

1. **Icon-Only Buttons** - All icon-only buttons have `aria-label` attributes
2. **Decorative Icons** - All decorative icons have `aria-hidden="true"`
3. **Form Validation** - Form fields with errors use `aria-describedby` and `aria-invalid`
4. **Dynamic Content** - Alerts and notifications use `aria-live` regions
5. **Modal Dialogs** - Modals have proper `role`, `aria-labelledby`, and `aria-hidden`
6. **Navigation** - Navigation elements have `aria-label`, `aria-expanded`, and `aria-current`
7. **Tables** - Tables have captions and proper `scope` attributes
8. **Loading States** - Loading buttons use `aria-busy` attribute

## ARIA Attributes Reference

### 1. Icon-Only Buttons

**Purpose:** Provide text alternatives for buttons that contain only icons.

**Implementation:**
```html
<!-- Edit Button -->
<button class="btn btn-primary" aria-label="Edit item">
    <i class="fa fa-edit" aria-hidden="true"></i>
</button>

<!-- Delete Button -->
<button class="btn btn-danger" aria-label="Delete item">
    <i class="fa fa-trash" aria-hidden="true"></i>
</button>

<!-- View Button -->
<button class="btn btn-info" aria-label="View details">
    <i class="fa fa-eye" aria-hidden="true"></i>
</button>
```

**Screen Reader Output:** "Edit item, button"

### 2. Form Validation with aria-describedby

**Purpose:** Associate error messages with form fields for screen reader users.

**Implementation:**
```html
<!-- Field with Error -->
<div class="form-group has-error">
    <label for="email">Email</label>
    <input type="email" id="email" class="form-control" 
           aria-invalid="true" 
           aria-describedby="email-error">
    <div id="email-error" class="error-message" role="alert" aria-live="polite">
        Please enter a valid email address
    </div>
</div>

<!-- Required Field -->
<div class="form-group">
    <label for="username">Username <span class="required">*</span></label>
    <input type="text" id="username" class="form-control" 
           required 
           aria-required="true" 
           aria-describedby="username-help">
    <small id="username-help" class="form-text text-muted">
        Enter your username (required field)
    </small>
</div>
```

**Screen Reader Output:** "Email, invalid entry, edit text. Please enter a valid email address"

### 3. Dynamic Content with aria-live

**Purpose:** Announce dynamic content changes to screen reader users.

**Implementation:**
```html
<!-- Success Alert (Polite) -->
<div class="alert alert-success" role="alert" aria-live="polite">
    <i class="fa fa-check-circle" aria-hidden="true"></i>
    <strong>Success!</strong> Your changes have been saved.
</div>

<!-- Error Alert (Assertive) -->
<div class="alert alert-danger" role="alert" aria-live="assertive">
    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
    <strong>Error!</strong> Unable to process your request.
</div>

<!-- Loading State -->
<button class="btn btn-primary" aria-busy="true" aria-live="polite">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
    <span class="sr-only">Loading...</span>
    Processing
</button>

<!-- Status Update Region -->
<div role="status" aria-live="polite" aria-atomic="true">
    <span id="status-text">Uploading file... 45% complete</span>
</div>
```

**aria-live Values:**
- `polite` - Announces after current speech (for non-critical updates)
- `assertive` - Interrupts current speech (for critical errors)

**Screen Reader Output:** 
- Polite: Announced after current speech completes
- Assertive: Announced immediately, interrupting current speech

### 4. Decorative Icons with aria-hidden

**Purpose:** Hide decorative icons from screen readers to avoid redundant announcements.

**Implementation:**
```html
<!-- Button with Icon and Text -->
<button class="btn btn-primary">
    <i class="fa fa-save" aria-hidden="true"></i>
    Save Changes
</button>

<!-- Alert with Icon -->
<div class="alert alert-warning" role="alert">
    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
    <strong>Warning:</strong> This action cannot be undone.
</div>
```

**Screen Reader Output:** "Save Changes, button" (icon is hidden)

### 5. Modal Dialogs

**Purpose:** Properly identify and label modal dialogs for screen reader users.

**Implementation:**
```html
<div class="modal fade" id="confirmModal" 
     tabindex="-1" 
     role="dialog" 
     aria-labelledby="confirmModalTitle" 
     aria-hidden="true" 
     aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close dialog">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>
```

**Key Attributes:**
- `role="dialog"` - Identifies the modal as a dialog
- `aria-labelledby` - References the modal title
- `aria-hidden="true"` - Hides modal when closed
- `aria-modal="true"` - Indicates this is a modal dialog
- `tabindex="-1"` - Allows focus management

**Screen Reader Output:** "Confirm Action, dialog"

### 6. Navigation

**Purpose:** Provide context and state information for navigation elements.

**Implementation:**
```html
<!-- Dropdown Menu -->
<button class="btn btn-default dropdown-toggle" 
        type="button" 
        data-toggle="dropdown" 
        aria-haspopup="true" 
        aria-expanded="false">
    Options
    <i class="fa fa-caret-down" aria-hidden="true"></i>
</button>
<ul class="dropdown-menu" role="menu" aria-label="Options menu">
    <li role="menuitem"><a href="#">Action 1</a></li>
    <li role="menuitem"><a href="#">Action 2</a></li>
</ul>

<!-- Breadcrumb Navigation -->
<nav aria-label="Breadcrumb navigation" role="navigation">
    <ol class="breadcrumb">
        <li><a href="#">Home</a></li>
        <li><a href="#">Products</a></li>
        <li aria-current="page">Current Page</li>
    </ol>
</nav>

<!-- Pagination -->
<nav aria-label="Page navigation" role="navigation">
    <ul class="pagination">
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Go to previous page">
                <i class="fa fa-chevron-left" aria-hidden="true"></i>
                <span>Prev</span>
            </a>
        </li>
        <li class="page-item active">
            <span class="page-link" aria-current="page" aria-label="Current page, page 1">1</span>
        </li>
        <li class="page-item">
            <a class="page-link" href="#" aria-label="Go to page 2">2</a>
        </li>
    </ul>
</nav>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" 
        data-toggle="push-menu" 
        role="button" 
        aria-label="Toggle navigation menu" 
        aria-expanded="false" 
        aria-controls="customer-sidebar">
    <i class="fa fa-bars" aria-hidden="true"></i>
    <span class="sr-only">Toggle navigation</span>
</button>
```

**Key Attributes:**
- `aria-haspopup` - Indicates element has a popup menu
- `aria-expanded` - Indicates expanded/collapsed state
- `aria-current="page"` - Indicates current page in navigation
- `aria-label` - Provides accessible name for navigation
- `aria-controls` - References the controlled element

### 7. Tables

**Purpose:** Provide structure and context for data tables.

**Implementation:**
```html
<table class="table table-striped">
    <caption class="sr-only">Customer list</caption>
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>John Doe</td>
            <td>john@example.com</td>
            <td>Active</td>
            <td>
                <button class="btn btn-sm btn-primary" aria-label="Edit John Doe">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </button>
                <button class="btn btn-sm btn-danger" aria-label="Delete John Doe">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>
```

**Key Attributes:**
- `<caption>` - Provides table description (can be visually hidden with `.sr-only`)
- `scope="col"` - Indicates header applies to column
- `scope="row"` - Indicates header applies to row

**Screen Reader Output:** "Customer list, table with 1 row and 4 columns"

### 8. Loading States

**Purpose:** Indicate when content is loading or processing.

**Implementation:**
```html
<!-- Loading Button -->
<button class="btn btn-primary" aria-busy="true" aria-live="polite">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
    <span class="sr-only">Loading...</span>
    Processing
</button>

<!-- Loading Region -->
<div role="status" aria-busy="true" aria-live="polite">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
    <span>Loading content...</span>
</div>
```

**Key Attributes:**
- `aria-busy="true"` - Indicates element is being updated
- `role="status"` - Identifies status message region

## JavaScript Enhancement

The `aria-enhancements.js` script automatically adds ARIA attributes to elements that don't have them:

### Features:

1. **Automatic Icon Button Labeling** - Detects icon-only buttons and adds appropriate `aria-label`
2. **Decorative Icon Hiding** - Adds `aria-hidden="true"` to decorative icons
3. **Form Validation** - Manages `aria-invalid` and `aria-describedby` for form errors
4. **Modal Focus Management** - Handles focus trapping and restoration in modals
5. **Dynamic Content Monitoring** - Adds `aria-live` to dynamic content regions
6. **Navigation State Management** - Updates `aria-expanded` for dropdowns and menus
7. **Table Enhancement** - Adds captions and scope attributes to tables

### Usage:

The script runs automatically on page load. No manual initialization required.

```javascript
// The script is included in the header
<script src="{$app_url}/ui/ui/scripts/aria-enhancements.js"></script>
```

## Testing ARIA Implementation

### Manual Testing:

1. **Keyboard Navigation** - Tab through all interactive elements
2. **Screen Reader Testing** - Use NVDA (Windows) or VoiceOver (Mac)
3. **Browser DevTools** - Check Accessibility tree in Chrome/Firefox DevTools

### Automated Testing:

1. **axe DevTools** - Browser extension for accessibility testing
2. **Lighthouse** - Run accessibility audit in Chrome DevTools
3. **WAVE** - Web accessibility evaluation tool

### Test File:

Open `ui/ui/styles/test-aria-attributes.html` in a browser to run automated ARIA tests.

## Best Practices

### DO:

✅ Use `aria-label` for icon-only buttons
✅ Use `aria-hidden="true"` for decorative icons
✅ Use `aria-describedby` to associate error messages with form fields
✅ Use `aria-live="polite"` for non-critical updates
✅ Use `aria-live="assertive"` for critical errors
✅ Use `role="alert"` for important messages
✅ Use `aria-current="page"` for current navigation item
✅ Use `aria-expanded` for expandable elements
✅ Provide table captions (can be visually hidden)

### DON'T:

❌ Don't use ARIA when native HTML is sufficient
❌ Don't add `aria-label` to elements that already have visible text
❌ Don't forget to update `aria-expanded` when toggling elements
❌ Don't use `aria-hidden="true"` on focusable elements
❌ Don't rely solely on color to convey information
❌ Don't use `role="button"` on actual `<button>` elements
❌ Don't forget to manage focus in modals and dialogs

## Common Icon Mappings

The JavaScript automatically maps common icons to labels:

| Icon Class | Label |
|------------|-------|
| `fa-edit`, `fa-pencil` | Edit |
| `fa-trash`, `fa-trash-o` | Delete |
| `fa-eye` | View |
| `fa-download` | Download |
| `fa-print` | Print |
| `fa-save` | Save |
| `fa-plus` | Add |
| `fa-minus` | Remove |
| `fa-close`, `fa-times` | Close |
| `fa-check` | Confirm |
| `fa-refresh` | Refresh |
| `fa-search` | Search |
| `fa-filter` | Filter |
| `fa-cog` | Settings |
| `glyphicon-qrcode` | Scan QR code |

## Resources

- [WAI-ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [ARIA in HTML](https://www.w3.org/TR/html-aria/)
- [MDN ARIA Documentation](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA)
- [WebAIM ARIA Techniques](https://webaim.org/techniques/aria/)

## Support

For questions or issues related to ARIA implementation, please refer to:
- Requirements Document: `.kiro/specs/ui-styling-transformation/requirements.md`
- Design Document: `.kiro/specs/ui-styling-transformation/design.md`
- Test File: `ui/ui/styles/test-aria-attributes.html`
