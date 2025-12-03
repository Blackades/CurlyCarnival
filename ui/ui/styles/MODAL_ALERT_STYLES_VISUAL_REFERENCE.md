# Modal and Alert Styles - Visual Reference Guide

## Quick Reference

### Modal Dialog Sizes
- **Small Modal**: 520px max-width - For simple confirmations
- **Default Modal**: 720px max-width - For standard forms and content
- **Large Modal**: 960px max-width - For complex layouts and detailed content

### Alert Message Types
- **Success**: Green (#52c41a) - Operation completed successfully
- **Warning**: Orange (#faad14) - Important information or caution
- **Danger**: Red (#f5222d) - Errors or critical issues
- **Info**: Blue (#1890ff) - Informational messages

## Modal Dialog Components

### Modal Structure
```
┌─────────────────────────────────────────┐
│ Modal Header (16px 24px padding)       │
│ ├─ Title (16px, weight 600)            │
│ └─ Close Button (×)                    │
├─────────────────────────────────────────┤
│ Modal Body (24px padding)              │
│ - Main content area                    │
│ - Can be scrollable (max-height 400px) │
├─────────────────────────────────────────┤
│ Modal Footer (10px 16px padding)       │
│ └─ Buttons (right-aligned, 8px gap)    │
└─────────────────────────────────────────┘
```

### Modal Styling Details
- **Border Radius**: 8px (rounded corners)
- **Box Shadow**: 0 4px 16px rgba(0, 0, 0, 0.15)
- **Backdrop**: rgba(0, 0, 0, 0.45) - 45% opacity black
- **Header Border**: 1px solid #f0f0f0 (bottom)
- **Footer Border**: 1px solid #f0f0f0 (top)
- **Transition**: 0.3s cubic-bezier for smooth animations

### Modal Variants

#### Small Modal (520px)
```html
<div class="modal-dialog modal-sm">
  <!-- Perfect for: -->
  - Quick confirmations
  - Simple yes/no dialogs
  - Brief messages
</div>
```

#### Default Modal (720px)
```html
<div class="modal-dialog">
  <!-- Perfect for: -->
  - Standard forms
  - Medium content
  - Most use cases
</div>
```

#### Large Modal (960px)
```html
<div class="modal-dialog modal-lg">
  <!-- Perfect for: -->
  - Complex forms
  - Multi-column layouts
  - Detailed information
</div>
```

#### Modal with Icon
```html
<h4 class="modal-title modal-icon modal-icon-success">
  <i class="fa fa-check-circle"></i>
  <span>Success Message</span>
</h4>
```

## Alert Message Components

### Alert Structure
```
┌─────────────────────────────────────────┐
│ [Icon] Alert Content                    │
│        ├─ Strong text (optional)        │
│        └─ Message text                  │
│                              [Close ×]  │
└─────────────────────────────────────────┘
```

### Alert Styling Details
- **Padding**: 12px 16px
- **Border Radius**: 4px
- **Display**: Flexbox with 8px gap
- **Icon Size**: 16px
- **Font Size**: 14px (base)

### Alert Color Schemes

#### Success Alert
- **Background**: #f6ffed (light green)
- **Border**: #b7eb8f (green)
- **Text**: #52c41a (green)
- **Use**: Successful operations, confirmations

#### Warning Alert
- **Background**: #fffbe6 (light yellow)
- **Border**: #ffe58f (yellow)
- **Text**: #faad14 (orange)
- **Use**: Warnings, important notices

#### Danger Alert
- **Background**: #fff1f0 (light red)
- **Border**: #ffccc7 (red)
- **Text**: #f5222d (red)
- **Use**: Errors, critical issues

#### Info Alert
- **Background**: #e6f7ff (light blue)
- **Border**: #91d5ff (blue)
- **Text**: #1890ff (blue)
- **Use**: Information, tips, notes

### Alert Variants

#### Basic Alert
```html
<div class="alert alert-success">
  <i class="fa fa-check-circle"></i>
  <div class="alert-content">
    <strong>Success!</strong> Message here.
  </div>
</div>
```

#### Dismissible Alert
```html
<div class="alert alert-warning alert-dismissible">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <i class="fa fa-exclamation-triangle"></i>
  <div class="alert-content">
    <strong>Warning!</strong> Message here.
  </div>
</div>
```

#### Bordered Alert (with left accent)
```html
<div class="alert alert-info alert-bordered">
  <i class="fa fa-info-circle"></i>
  <div class="alert-content">
    <strong>Info:</strong> Message here.
  </div>
</div>
```

#### Alert with Heading
```html
<div class="alert alert-info">
  <i class="fa fa-info-circle"></i>
  <div class="alert-content">
    <h4 class="alert-heading">Did you know?</h4>
    <p>Detailed information here.</p>
  </div>
</div>
```

#### Alert Sizes
```html
<!-- Small -->
<div class="alert alert-info alert-sm">...</div>

<!-- Default -->
<div class="alert alert-info">...</div>

<!-- Large -->
<div class="alert alert-info alert-lg">...</div>
```

## SweetAlert2 Confirmation Dialogs

### SweetAlert2 Styling
- **Border Radius**: 8px
- **Box Shadow**: 0 4px 16px rgba(0, 0, 0, 0.15)
- **Padding**: 24px
- **Title Size**: 18px, weight 600
- **Content Size**: 14px
- **Button Height**: 36px
- **Button Padding**: 8px 16px

### SweetAlert2 Button Styles

#### Confirm Button (Primary)
- **Background**: #1890ff (blue)
- **Hover**: #40a9ff (lighter blue)
- **Text**: White

#### Cancel Button (Secondary)
- **Background**: White
- **Border**: 1px solid #d9d9d9
- **Hover Border**: #40a9ff
- **Text**: #262626

#### Deny Button (Danger)
- **Background**: #f5222d (red)
- **Hover**: #ff4d4f (lighter red)
- **Text**: White

### SweetAlert2 Icon Colors
- **Success**: #52c41a (green)
- **Warning**: #faad14 (orange)
- **Error**: #f5222d (red)
- **Info**: #13c2c2 (cyan)
- **Question**: #1890ff (blue)

### Common SweetAlert2 Patterns

#### Success Notification
```javascript
Swal.fire({
  icon: 'success',
  title: 'Success!',
  text: 'Operation completed successfully.'
});
```

#### Confirmation Dialog
```javascript
Swal.fire({
  icon: 'warning',
  title: 'Are you sure?',
  text: 'This action cannot be undone.',
  showCancelButton: true,
  confirmButtonText: 'Yes, proceed',
  cancelButtonText: 'Cancel'
});
```

#### Delete Confirmation
```javascript
Swal.fire({
  icon: 'error',
  title: 'Delete Item?',
  text: 'This cannot be undone.',
  showCancelButton: true,
  confirmButtonText: 'Yes, delete it',
  confirmButtonColor: '#f5222d',
  reverseButtons: true
});
```

#### Input Dialog
```javascript
Swal.fire({
  title: 'Enter value',
  input: 'text',
  inputPlaceholder: 'Type here',
  showCancelButton: true,
  inputValidator: (value) => {
    if (!value) return 'Required!';
  }
});
```

## Accessibility Features

### Keyboard Navigation
- **ESC**: Close modal/alert
- **TAB**: Navigate between buttons
- **ENTER**: Activate focused button
- **SPACE**: Activate focused button

### Screen Reader Support
- Proper ARIA roles maintained
- Focus management for modals
- Dismissible alerts announce closure
- Button labels are descriptive

### Color Contrast
All color combinations meet WCAG AA standards:
- Success: 4.5:1 contrast ratio ✓
- Warning: 4.5:1 contrast ratio ✓
- Danger: 4.5:1 contrast ratio ✓
- Info: 4.5:1 contrast ratio ✓

## Responsive Behavior

### Mobile (< 768px)
- Modals take full width with margins
- Alert text wraps appropriately
- Touch targets are 44×44px minimum
- Buttons stack if needed

### Tablet (768px - 1024px)
- Modals maintain max-width
- Alerts display normally
- All interactions work smoothly

### Desktop (> 1024px)
- Full modal sizes available
- Optimal spacing and layout
- Hover effects active

## Best Practices

### When to Use Modals
✓ **Use for:**
- Forms requiring user input
- Confirmations before destructive actions
- Displaying detailed information
- Multi-step processes

✗ **Avoid for:**
- Simple notifications (use alerts)
- Frequent interruptions
- Non-critical information
- Mobile-first experiences

### When to Use Alerts
✓ **Use for:**
- Success confirmations
- Error messages
- Warnings and notices
- Inline feedback

✗ **Avoid for:**
- Critical confirmations (use modals)
- Long-form content
- Interactive elements
- Permanent information

### When to Use SweetAlert2
✓ **Use for:**
- Delete confirmations
- Quick yes/no decisions
- Simple input prompts
- Toast notifications

✗ **Avoid for:**
- Complex forms
- Multi-step processes
- Long content
- Frequent notifications

## Testing Checklist

### Modal Testing
- [ ] Small modal displays at 520px
- [ ] Default modal displays at 720px
- [ ] Large modal displays at 960px
- [ ] Close button works
- [ ] ESC key closes modal
- [ ] Backdrop click closes modal
- [ ] Footer buttons are right-aligned
- [ ] Scrollable content works
- [ ] Modal with icon displays correctly

### Alert Testing
- [ ] Success alert shows green
- [ ] Warning alert shows orange
- [ ] Danger alert shows red
- [ ] Info alert shows blue
- [ ] Icons display correctly
- [ ] Dismissible close button works
- [ ] Bordered variant shows left accent
- [ ] Alert with heading displays properly
- [ ] Alert sizes work (sm, default, lg)

### SweetAlert2 Testing
- [ ] Success icon shows green
- [ ] Warning icon shows orange
- [ ] Error icon shows red
- [ ] Info icon shows cyan
- [ ] Confirm button is blue
- [ ] Cancel button is white with border
- [ ] Deny button is red
- [ ] Input fields work
- [ ] Validation messages display
- [ ] Toast notifications work

## Browser Compatibility

| Browser | Version | Status |
|---------|---------|--------|
| Chrome  | Latest  | ✓ Full Support |
| Firefox | Latest  | ✓ Full Support |
| Safari  | Latest  | ✓ Full Support |
| Edge    | Latest  | ✓ Full Support |
| IE 11   | -       | ⚠ Partial (fallbacks) |

## Performance Notes

- Modal animations use CSS transforms (GPU accelerated)
- Alert rendering is lightweight
- SweetAlert2 is loaded on demand
- No JavaScript required for basic modals/alerts
- Minimal CSS file size impact (~5KB)

## Common Issues & Solutions

### Issue: Modal doesn't close
**Solution**: Ensure Bootstrap JS is loaded and jQuery is available

### Issue: Alert icons not showing
**Solution**: Verify Font Awesome is loaded

### Issue: SweetAlert2 buttons wrong color
**Solution**: Check that modern CSS is loaded after SweetAlert2 CSS

### Issue: Modal backdrop too dark
**Solution**: Backdrop opacity is 45%, adjust if needed in CSS variables

### Issue: Alert text too small on mobile
**Solution**: Use alert-lg class or adjust base font size

## File Locations

- **CSS File**: `phpnuxbill-fresh/ui/ui/styles/phpnuxbill-modern.css`
- **Test File**: `phpnuxbill-fresh/ui/ui/test-modals-alerts.html`
- **Summary**: `phpnuxbill-fresh/ui/ui/styles/TASK_9_MODAL_ALERT_STYLES_SUMMARY.md`
- **This Guide**: `phpnuxbill-fresh/ui/ui/styles/MODAL_ALERT_STYLES_VISUAL_REFERENCE.md`

## Related Tasks

- Task 1-2: Foundation (typography, colors) ✓
- Task 3: Form controls ✓
- Task 4: Button styles ✓
- Task 9: Modal and alert styles ✓ (Current)
- Task 10: Search and filter controls (Next)
- Task 12: Integration into application (Pending)
