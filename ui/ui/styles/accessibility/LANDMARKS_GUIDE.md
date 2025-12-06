# HTML5 Landmarks and ARIA Roles Guide

## Overview
This document provides guidelines for implementing semantic HTML5 landmarks and ARIA roles throughout the PHPNuxBill application to improve accessibility and navigation.

## What are Landmarks?

Landmarks are semantic regions of a page that help assistive technology users navigate and understand page structure. They can be created using:
1. HTML5 semantic elements (preferred)
2. ARIA role attributes (for backward compatibility or when HTML5 elements aren't suitable)

## Core Landmarks

### 1. Banner (Header)
**Purpose:** Site header with logo, navigation, and global utilities

**HTML5 Implementation:**
```html
<header role="banner">
    <!-- Logo, main navigation, user menu -->
</header>
```

**Usage in PHPNuxBill:**
- Admin header: `<header class="main-header admin-header" role="banner">`
- Customer header: `<header class="main-header header-fixed" role="banner">`

**Rules:**
- Only ONE banner per page
- Should contain site-wide elements (logo, main nav, user menu)
- Typically at the top of the page

### 2. Navigation
**Purpose:** Collection of navigational links

**HTML5 Implementation:**
```html
<nav role="navigation" aria-label="Main navigation">
    <ul>
        <li><a href="#">Dashboard</a></li>
        <li><a href="#">Customers</a></li>
    </ul>
</nav>
```

**Usage in PHPNuxBill:**
- Sidebar: `<aside id="main-sidebar" class="main-sidebar" role="navigation" aria-label="Main navigation">`
- Top nav: `<nav class="navbar navbar-static-top" role="navigation">`

**Rules:**
- Multiple navigation landmarks allowed
- Use `aria-label` to distinguish multiple nav regions
- Common labels: "Main navigation", "Secondary navigation", "Breadcrumb navigation"

### 3. Main Content
**Purpose:** Primary content of the page

**HTML5 Implementation:**
```html
<main role="main" id="main-content" aria-label="Main content">
    <!-- Primary page content -->
</main>
```

**Usage in PHPNuxBill:**
- Admin: `<div class="content-wrapper main-content" role="main" id="main-content">`
- Customer: `<main class="content-main" role="main" id="main-content">`

**Rules:**
- Only ONE main landmark per page
- Should contain the primary content
- Skip link target: `<a href="#main-content">Skip to main content</a>`

### 4. Complementary (Sidebar)
**Purpose:** Supporting content related to main content

**HTML5 Implementation:**
```html
<aside role="complementary" aria-label="Related information">
    <!-- Sidebar content, related links, widgets -->
</aside>
```

**Usage in PHPNuxBill:**
- Used for sidebars with supplementary information
- Not used for main navigation sidebars (those use `role="navigation"`)

**Rules:**
- Multiple complementary regions allowed
- Use `aria-label` to distinguish multiple regions
- Should be related to main content

### 5. Contentinfo (Footer)
**Purpose:** Site footer with copyright, links, contact info

**HTML5 Implementation:**
```html
<footer role="contentinfo">
    <!-- Copyright, footer links, contact info -->
</footer>
```

**Usage in PHPNuxBill:**
- Admin footer: `<footer class="main-footer" role="contentinfo">`
- Customer footer: `<footer class="main-footer" role="contentinfo">`

**Rules:**
- Only ONE contentinfo per page
- Should contain site-wide footer information
- Typically at the bottom of the page

### 6. Search
**Purpose:** Search functionality

**HTML5 Implementation:**
```html
<div role="search" aria-label="Site search">
    <form>
        <input type="search" aria-label="Search query">
        <button type="submit">Search</button>
    </form>
</div>
```

**Usage in PHPNuxBill:**
- Search overlays and forms
- Table search/filter sections

**Rules:**
- Multiple search regions allowed
- Use `aria-label` to describe what can be searched

### 7. Form
**Purpose:** Form region

**HTML5 Implementation:**
```html
<form role="form" aria-label="Customer registration">
    <!-- Form fields -->
</form>
```

**Usage in PHPNuxBill:**
- Login forms
- Registration forms
- Data entry forms

**Rules:**
- Use `aria-label` or `aria-labelledby` to describe form purpose
- Not needed for simple forms (browser provides implicit role)

### 8. Region
**Purpose:** Generic landmark for significant page sections

**HTML5 Implementation:**
```html
<section role="region" aria-labelledby="stats-heading">
    <h2 id="stats-heading">Statistics</h2>
    <!-- Section content -->
</section>
```

**Usage in PHPNuxBill:**
- Dashboard sections
- Grouped content areas
- Significant page sections

**Rules:**
- Must have accessible name (aria-label or aria-labelledby)
- Use sparingly - only for significant sections
- Consider if another landmark type is more appropriate

## Implementation in PHPNuxBill

### Admin Layout Structure
```html
<body>
    <!-- Skip Links -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <a href="#main-sidebar" class="skip-link">Skip to navigation</a>
    
    <div class="wrapper">
        <!-- Banner (Header) -->
        <header class="main-header" role="banner">
            <!-- Logo, top nav, user menu -->
        </header>
        
        <!-- Navigation (Sidebar) -->
        <aside id="main-sidebar" class="main-sidebar" role="navigation" aria-label="Main navigation">
            <!-- Sidebar menu -->
        </aside>
        
        <!-- Main Content -->
        <div class="content-wrapper" role="main" id="main-content" aria-label="Main content">
            <section class="content-header">
                <h1>{$_title}</h1>
            </section>
            <section class="content">
                <!-- Page content -->
            </section>
        </div>
        
        <!-- Footer -->
        <footer class="main-footer" role="contentinfo">
            <!-- Copyright, links -->
        </footer>
    </div>
</body>
```

### Customer Layout Structure
```html
<body>
    <!-- Skip Links -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <a href="#customer-sidebar" class="skip-link">Skip to navigation</a>
    
    <div class="app-wrapper">
        <!-- Banner (Header) -->
        <header class="main-header" role="banner">
            <!-- Logo, top nav, user menu -->
        </header>
        
        <!-- Navigation (Sidebar) -->
        <aside id="customer-sidebar" class="main-sidebar" role="navigation" aria-label="Customer navigation">
            <!-- Sidebar menu -->
        </aside>
        
        <!-- Main Content -->
        <div class="content-wrapper">
            <header class="content-header">
                <h1>{$_title}</h1>
            </header>
            <main class="content-main" role="main" id="main-content" aria-label="Main content">
                <!-- Page content -->
            </main>
        </div>
        
        <!-- Footer -->
        <footer class="main-footer" role="contentinfo">
            <!-- Copyright, links -->
        </footer>
    </div>
</body>
```

## Best Practices

### 1. Use HTML5 Elements First
Prefer semantic HTML5 elements over ARIA roles:
- `<header>` instead of `<div role="banner">`
- `<nav>` instead of `<div role="navigation">`
- `<main>` instead of `<div role="main">`
- `<aside>` instead of `<div role="complementary">`
- `<footer>` instead of `<div role="contentinfo">`

### 2. Add ARIA Roles for Compatibility
Include explicit ARIA roles even when using HTML5 elements for better screen reader support:
```html
<header role="banner">
<nav role="navigation">
<main role="main">
```

### 3. Label Multiple Landmarks
When you have multiple landmarks of the same type, use `aria-label` or `aria-labelledby`:
```html
<nav role="navigation" aria-label="Main navigation">
<nav role="navigation" aria-label="Footer navigation">
```

### 4. Keep Landmarks Unique
- Only one `<main>` per page
- Only one `role="banner"` per page
- Only one `role="contentinfo"` per page
- Multiple `<nav>`, `<aside>`, `<section>` allowed

### 5. Don't Overuse Landmarks
Not every `<div>` needs a landmark role. Use landmarks for:
- Major page sections
- Significant content areas
- Navigation regions
- Search functionality

## Testing Landmarks

### Browser DevTools
```javascript
// List all landmarks
document.querySelectorAll('[role], header, nav, main, aside, footer, section').forEach(el => {
    console.log(el.tagName, el.getAttribute('role'), el.getAttribute('aria-label'));
});
```

### Screen Reader Testing
1. **NVDA (Windows):**
   - Press `D` to navigate by landmarks
   - Press `Insert + F7` to view elements list

2. **JAWS (Windows):**
   - Press `R` to navigate by landmarks
   - Press `Insert + F3` to view elements list

3. **VoiceOver (macOS):**
   - Press `VO + U` to open rotor
   - Select "Landmarks" from the menu

### Accessibility Testing Tools
- **WAVE:** Shows landmark structure visually
- **axe DevTools:** Validates landmark implementation
- **Lighthouse:** Checks for proper landmark usage

## Common Issues and Fixes

### Issue 1: Missing Main Landmark
**Problem:** Page has no `<main>` or `role="main"`
**Fix:** Wrap primary content in `<main role="main" id="main-content">`

### Issue 2: Multiple Main Landmarks
**Problem:** Page has multiple `<main>` elements
**Fix:** Keep only one main landmark per page

### Issue 3: Unlabeled Navigation
**Problem:** Multiple `<nav>` elements without labels
**Fix:** Add `aria-label` to distinguish them

### Issue 4: Missing Skip Links
**Problem:** No way to skip to main content
**Fix:** Add skip links at the top of the page

### Issue 5: Nested Landmarks
**Problem:** Landmarks nested incorrectly
**Fix:** Ensure proper nesting (e.g., `<nav>` inside `<header>` is OK)

## Keyboard Navigation

Landmarks improve keyboard navigation:
1. Screen readers can jump between landmarks
2. Skip links allow jumping to main content
3. Users can navigate by landmark type
4. Reduces time to find relevant content

## Screen Reader Announcements

When properly implemented, screen readers announce:
- "Banner" or "Header" for `<header role="banner">`
- "Navigation" for `<nav role="navigation">`
- "Main" or "Main content" for `<main role="main">`
- "Complementary" for `<aside role="complementary">`
- "Content information" or "Footer" for `<footer role="contentinfo">`

## Maintenance Checklist

- [ ] All pages have exactly one `<main>` landmark
- [ ] All pages have exactly one `role="banner"`
- [ ] All pages have exactly one `role="contentinfo"`
- [ ] Navigation regions use `<nav>` or `role="navigation"`
- [ ] Multiple landmarks of same type have unique labels
- [ ] Skip links point to valid landmark IDs
- [ ] Landmarks follow logical page structure
- [ ] No unnecessary landmark roles on minor elements

## References

- [ARIA Landmarks Example](https://www.w3.org/WAI/ARIA/apg/patterns/landmarks/)
- [WebAIM - Semantic Structure](https://webaim.org/techniques/semanticstructure/)
- [MDN - ARIA Roles](https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles)
- [WCAG 2.1 - Bypass Blocks](https://www.w3.org/WAI/WCAG21/Understanding/bypass-blocks.html)

---

**Last Updated:** December 2025
**Requirement:** 6.5 - Add semantic HTML5 landmarks
