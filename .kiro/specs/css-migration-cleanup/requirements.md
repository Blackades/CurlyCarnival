# Requirements Document: CSS Migration Cleanup

## Introduction

This specification defines the requirements for cleaning up CSS file references after the UI styling transformation. The new modern CSS system has been implemented, but template files still reference old CSS files that no longer exist, causing 404 errors and potential styling issues. This cleanup ensures all templates properly load the new CSS architecture while maintaining functionality.

## Glossary

- **Legacy CSS Files**: Old CSS files (bootstrap.min.css, modern-AdminLTE.min.css, phpnuxbill.customer.css, etc.) that were replaced by the new modular CSS system
- **Modern CSS System**: The new modular CSS architecture based on phpnuxbill-modern.css with organized base, layout, component, utility, and responsive modules
- **Template Files**: Smarty template files (.tpl) that include CSS file references in their head sections
- **404 Errors**: HTTP errors indicating requested CSS files don't exist on the server
- **CSS Dependencies**: External libraries and plugins (SweetAlert2, Select2, etc.) that need to remain functional

## Requirements

### Requirement 1: Remove Legacy CSS References

**User Story:** As a developer, I want to remove all references to non-existent legacy CSS files, so that the browser console is clean and no 404 errors occur

#### Acceptance Criteria

1. THE Application SHALL remove all references to bootstrap.min.css from template files
2. THE Application SHALL remove all references to modern-AdminLTE.min.css from template files
3. THE Application SHALL remove all references to phpnuxbill.customer.css from template files
4. THE Application SHALL remove all references to phpnux.omer.css from template files
5. THE Application SHALL verify no 404 errors appear in browser console after cleanup

### Requirement 2: Ensure Modern CSS System Loading

**User Story:** As a developer, I want to ensure the new modern CSS system loads correctly, so that all styling works as designed

#### Acceptance Criteria

1. THE Application SHALL ensure phpnuxbill-modern.css is loaded in all template files
2. THE Application SHALL load phpnuxbill-modern.css with a cache-busting version parameter
3. THE Application SHALL load the modern CSS file before any page-specific styles
4. THE Application SHALL verify all CSS modules are properly imported in phpnuxbill-modern.css
5. THE Application SHALL ensure the CSS load order is: base → layout → components → utilities → responsive

### Requirement 3: Maintain Plugin CSS Dependencies

**User Story:** As a developer, I want to keep necessary plugin CSS files, so that third-party components continue to work

#### Acceptance Criteria

1. THE Application SHALL retain sweetalert2.min.css reference for notification functionality
2. THE Application SHALL retain select2.min.css and select2-bootstrap.min.css for dropdown functionality
3. THE Application SHALL retain icon font CSS files (ionicons, font-awesome)
4. THE Application SHALL retain summernote.min.css for rich text editing
5. THE Application SHALL verify all retained plugins function correctly after cleanup

### Requirement 4: Fix Profile Icon Sizing

**User Story:** As a user, I want the profile icon to be appropriately sized, so that it doesn't dominate the screen

#### Acceptance Criteria

1. THE Application SHALL ensure profile photo displays at 96px × 96px on desktop
2. THE Application SHALL ensure profile photo displays at 88px × 88px on tablet
3. THE Application SHALL ensure profile photo displays at 80px × 80px on mobile
4. THE Application SHALL apply the profile-photo-display CSS class correctly
5. THE Application SHALL verify the profile icon size is visually appropriate across all breakpoints

### Requirement 5: Standardize CSS Loading Across Templates

**User Story:** As a developer, I want consistent CSS loading across all templates, so that maintenance is easier

#### Acceptance Criteria

1. THE Application SHALL use the same CSS loading pattern in customer templates
2. THE Application SHALL use the same CSS loading pattern in admin templates
3. THE Application SHALL document the standard CSS loading order
4. THE Application SHALL include accessibility CSS (skip-links.css) in all templates
5. THE Application SHALL use consistent version parameters for cache busting

### Requirement 6: Verify Styling Integrity

**User Story:** As a user, I want all UI components to display correctly, so that the application is usable

#### Acceptance Criteria

1. THE Application SHALL verify buttons display with correct styling
2. THE Application SHALL verify forms display with correct styling
3. THE Application SHALL verify tables display with correct styling
4. THE Application SHALL verify cards display with correct styling
5. THE Application SHALL verify navigation displays with correct styling
6. THE Application SHALL verify modals display with correct styling
7. THE Application SHALL verify alerts display with correct styling

### Requirement 7: Maintain Responsive Behavior

**User Story:** As a user, I want the application to work on all devices, so that I can access it from anywhere

#### Acceptance Criteria

1. THE Application SHALL ensure mobile styles load correctly
2. THE Application SHALL ensure tablet styles load correctly
3. THE Application SHALL ensure desktop styles load correctly
4. THE Application SHALL verify responsive breakpoints work as designed
5. THE Application SHALL ensure no horizontal scrolling occurs on any device

### Requirement 8: Preserve Functionality

**User Story:** As a user, I want all features to continue working, so that the cleanup doesn't break anything

#### Acceptance Criteria

1. THE Application SHALL ensure all forms submit correctly after CSS cleanup
2. THE Application SHALL ensure all buttons trigger correct actions after CSS cleanup
3. THE Application SHALL ensure all navigation links work after CSS cleanup
4. THE Application SHALL ensure all modals open and close correctly after CSS cleanup
5. THE Application SHALL ensure all JavaScript functionality remains intact after CSS cleanup

### Requirement 9: Clean Browser Console

**User Story:** As a developer, I want a clean browser console, so that I can easily identify real issues

#### Acceptance Criteria

1. THE Application SHALL produce zero 404 errors for CSS files
2. THE Application SHALL produce zero CSS parsing errors
3. THE Application SHALL produce zero missing font errors
4. THE Application SHALL load all CSS files successfully
5. THE Application SHALL verify console cleanliness across all major pages

### Requirement 10: Documentation and Maintenance

**User Story:** As a developer, I want clear documentation, so that future CSS updates are straightforward

#### Acceptance Criteria

1. THE Application SHALL document which CSS files are required
2. THE Application SHALL document the CSS loading order
3. THE Application SHALL document how to add new CSS modules
4. THE Application SHALL document the version parameter strategy
5. THE Application SHALL provide a CSS troubleshooting guide
