# Heading Hierarchy Guide

## Overview
This document provides guidelines for maintaining proper heading hierarchy throughout the PHPNuxBill application to ensure accessibility compliance.

## Heading Hierarchy Rules

### 1. Single H1 Per Page
- Each page should have exactly ONE `<h1>` element
- The `<h1>` should represent the main page title
- In templates, this is typically: `<h1 class="page-title">{$_title}</h1>`

### 2. Sequential Hierarchy
- Headings should follow a logical sequence: h1 → h2 → h3 → h4 → h5 → h6
- Never skip heading levels (e.g., don't go from h2 to h4)
- You can go back up the hierarchy (e.g., h4 → h2 is acceptable)

### 3. Semantic Structure
```
h1 - Page Title (Main heading)
  h2 - Major Section
    h3 - Subsection
      h4 - Sub-subsection
        h5 - Minor heading
          h6 - Smallest heading
```

## Implementation in Templates

### Admin Templates
```smarty
<!-- Page Title (H1) -->
<h1 class="page-title">{$_title}</h1>

<!-- Section Headings (H2) -->
<h2>Customer Information</h2>

<!-- Subsection Headings (H3) -->
<h3>Contact Details</h3>

<!-- Card/Panel Titles (H3 or H4) -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">Recent Activity</h3>
    </div>
</div>
```

### Customer Templates
```smarty
<!-- Page Title (H1) -->
<h1 class="page-title">{$_title}</h1>

<!-- Dashboard Sections (H2) -->
<h2>Your Services</h2>

<!-- Service Cards (H3) -->
<h3>Active Internet Plan</h3>
```

## Common Patterns

### Dashboard Pages
```
h1: Dashboard
  h2: Statistics Overview
  h2: Recent Activity
    h3: Today's Transactions
    h3: Recent Customers
  h2: Quick Actions
```

### List Pages
```
h1: Customer List
  h2: Search and Filters
  h2: Customer Table
  h2: Pagination
```

### Form Pages
```
h1: Add New Customer
  h2: Personal Information
  h2: Contact Details
  h2: Service Configuration
```

### Detail Pages
```
h1: Customer Details - [Name]
  h2: Account Information
  h2: Active Services
    h3: Internet Plan
    h3: VPN Access
  h2: Payment History
```

## Verification Checklist

### Automated Testing
Use browser developer tools or accessibility testing tools:
1. Open browser DevTools
2. Run: `document.querySelectorAll('h1, h2, h3, h4, h5, h6')`
3. Verify hierarchy is sequential

### Manual Review
- [ ] Each page has exactly one h1
- [ ] h1 contains the main page title
- [ ] Headings follow sequential order
- [ ] No heading levels are skipped
- [ ] Section headings use h2
- [ ] Subsections use h3
- [ ] Card/panel titles use h3 or h4 appropriately

## Common Issues and Fixes

### Issue 1: Multiple H1 Elements
**Problem:** Page has multiple h1 elements
**Fix:** Change additional h1 elements to h2 or appropriate level

### Issue 2: Skipped Levels
**Problem:** Heading jumps from h2 to h4
**Fix:** Change h4 to h3, or add intermediate h3 if needed

### Issue 3: Heading Used for Styling
**Problem:** Using h3 just because it looks right
**Fix:** Use appropriate heading level and style with CSS

### Issue 4: No H1 on Page
**Problem:** Page starts with h2 or h3
**Fix:** Add h1 for main page title

## CSS Styling

Headings should be styled consistently regardless of their level:

```css
/* Don't rely on default browser styles */
h1, h2, h3, h4, h5, h6 {
    /* Reset and apply custom styles */
}

/* Style by context, not just by tag */
.page-title { /* h1 */ }
.section-heading { /* h2 */ }
.subsection-heading { /* h3 */ }
.panel-title { /* h3 or h4 */ }
```

## Screen Reader Experience

Proper heading hierarchy allows screen reader users to:
1. Navigate by headings (common keyboard shortcut: H key)
2. Jump to specific sections quickly
3. Understand page structure and content organization
4. Skip to relevant content efficiently

## Testing Tools

### Browser Extensions
- WAVE (Web Accessibility Evaluation Tool)
- axe DevTools
- Lighthouse (Chrome DevTools)

### Screen Readers
- NVDA (Windows) - Free
- JAWS (Windows) - Commercial
- VoiceOver (macOS/iOS) - Built-in

### Keyboard Testing
1. Press H key repeatedly to navigate through headings
2. Verify logical flow and hierarchy
3. Ensure no important content is missed

## References

- [WCAG 2.1 - Headings and Labels](https://www.w3.org/WAI/WCAG21/Understanding/headings-and-labels.html)
- [WebAIM - Semantic Structure](https://webaim.org/techniques/semanticstructure/)
- [MDN - HTML Heading Elements](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Heading_Elements)

## Maintenance

This guide should be reviewed and updated:
- When adding new page templates
- When restructuring existing pages
- During accessibility audits
- When accessibility standards are updated

---

**Last Updated:** December 2025
**Requirement:** 6.5 - Maintain logical heading hierarchy
