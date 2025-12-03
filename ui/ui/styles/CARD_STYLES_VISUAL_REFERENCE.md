# Card & Panel Styles - Visual Reference Guide

## Quick Reference

### Basic Card Structure
```html
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Card Title</h3>
    </div>
    <div class="panel-body">
        Card content goes here
    </div>
    <div class="panel-footer">
        Footer content
    </div>
</div>
```

### AdminLTE Box Structure
```html
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">Box Title</h3>
        <div class="box-tools">
            <button class="btn btn-icon btn-sm">
                <i class="fa fa-cog"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        Box content goes here
    </div>
</div>
```

## Card Variants

### 1. Colored Header Cards
```html
<!-- Primary -->
<div class="panel panel-primary">...</div>

<!-- Success -->
<div class="panel panel-success">...</div>

<!-- Warning -->
<div class="panel panel-warning">...</div>

<!-- Danger -->
<div class="panel panel-danger">...</div>

<!-- Info -->
<div class="panel panel-info">...</div>
```

**Visual:** Header has solid color background with white text

### 2. Accent Cards
```html
<div class="panel panel-default card-accent accent-primary">
    <div class="panel-heading">...</div>
    <div class="panel-body">...</div>
</div>
```

**Visual:** 4px colored border at the top of the card

### 3. Compact Cards
```html
<div class="panel panel-compact">
    <div class="panel-heading">...</div>
    <div class="panel-body">...</div>
</div>
```

**Visual:** Reduced padding (16px instead of 24px)

### 4. Borderless Cards
```html
<div class="panel panel-borderless">
    <div class="panel-heading">...</div>
    <div class="panel-body">...</div>
</div>
```

**Visual:** Border instead of shadow

### 5. Flat Cards
```html
<div class="panel panel-flat">
    <div class="panel-heading">...</div>
    <div class="panel-body">...</div>
</div>
```

**Visual:** No shadow, completely flat

## Grid Layouts

### Auto-Fill Grid
```html
<div class="card-grid">
    <div class="panel">...</div>
    <div class="panel">...</div>
    <div class="panel">...</div>
</div>
```

**Behavior:** Automatically fills available space with minimum 300px cards

### Fixed Column Grids
```html
<!-- 2 Columns -->
<div class="card-grid-2">
    <div class="panel">...</div>
    <div class="panel">...</div>
</div>

<!-- 3 Columns -->
<div class="card-grid-3">
    <div class="panel">...</div>
    <div class="panel">...</div>
    <div class="panel">...</div>
</div>

<!-- 4 Columns -->
<div class="card-grid-4">
    <div class="panel">...</div>
    <div class="panel">...</div>
    <div class="panel">...</div>
    <div class="panel">...</div>
</div>
```

**Responsive:**
- 4-column → 3-column at 1200px
- 3-column → 2-column at 992px
- All → 1-column at 768px

### Flexbox Row Layout
```html
<div class="card-row">
    <div class="panel">...</div>
    <div class="panel">...</div>
    <div class="panel">...</div>
</div>
```

**Behavior:** Flexible row with wrapping, 33.33% width per card

## Metric Widgets

### Info Box
```html
<div class="info-box">
    <div class="info-box-icon bg-primary">
        <i class="fa fa-users"></i>
    </div>
    <div class="info-box-content">
        <div class="info-box-text">Total Users</div>
        <div class="info-box-number">1,234</div>
        <div class="info-box-subtext">+12% from last month</div>
    </div>
</div>
```

**Visual:**
- Horizontal layout
- 70×70px colored icon on left
- Large number (28px) with label
- Optional subtext

**Color Variants:** `.bg-primary`, `.bg-success`, `.bg-warning`, `.bg-danger`, `.bg-info`

### Small Box
```html
<div class="small-box bg-primary">
    <div class="small-box-inner">
        <h3>150</h3>
        <p>New Orders</p>
    </div>
    <div class="small-box-icon">
        <i class="fa fa-shopping-cart"></i>
    </div>
    <a href="#" class="small-box-footer">
        More info <i class="fa fa-arrow-circle-right"></i>
    </a>
</div>
```

**Visual:**
- Full-width colored background
- Large heading (36px)
- Background icon (80px, semi-transparent)
- Footer link

**Color Variants:** `.bg-primary`, `.bg-success`, `.bg-warning`, `.bg-danger`, `.bg-info`

### Metric Card
```html
<div class="metric-card">
    <div class="metric-card-icon bg-primary">
        <i class="fa fa-server"></i>
    </div>
    <div class="metric-card-value">24</div>
    <div class="metric-card-label">Routers</div>
    <div class="metric-card-change positive">
        <i class="fa fa-arrow-up"></i> 8.5%
    </div>
</div>
```

**Visual:**
- Centered layout
- 60×60px icon with light colored background
- Large value (32px)
- Uppercase label
- Change indicator (positive/negative/neutral)

**Icon Color Variants:** `.bg-primary`, `.bg-success`, `.bg-warning`, `.bg-danger`, `.bg-info`
**Change States:** `.positive` (green), `.negative` (red), `.neutral` (gray)

### Status Card
```html
<div class="status-card status-success">
    <div class="status-card-header">
        <h4 class="status-card-title">VPN Server Status</h4>
        <span class="badge bg-success">Online</span>
    </div>
    <div class="status-card-body">
        <div class="status-card-value">Running</div>
        <div class="status-card-description">
            All systems operational. 156 active connections.
        </div>
    </div>
</div>
```

**Visual:**
- 4px left border with status color
- Header with title and badge
- Large value text (28px)
- Description text

**Status Variants:** `.status-success`, `.status-warning`, `.status-danger`, `.status-info`

## Dashboard Widget Grid
```html
<div class="dashboard-widgets">
    <div class="info-box">...</div>
    <div class="info-box">...</div>
    <div class="info-box">...</div>
    <div class="info-box">...</div>
</div>
```

**Behavior:** Auto-fit grid with minimum 250px widgets, single column on mobile

## Interactive States

### Loading State
```html
<div class="panel panel-loading">
    <div class="panel-heading">...</div>
    <div class="panel-body">...</div>
</div>
```

**Visual:** Opacity reduced, spinner overlay, pointer events disabled

### Collapsible Card
```html
<div class="panel">
    <div class="panel-heading collapsed">
        <h3 class="panel-title">
            <a data-toggle="collapse" href="#collapse1">
                Title <i class="fa fa-chevron-down collapse-icon"></i>
            </a>
        </h3>
    </div>
    <div id="collapse1" class="panel-collapse collapse">
        <div class="panel-body">...</div>
    </div>
</div>
```

**Visual:** Icon rotates when collapsed/expanded

## Special Features

### Card with Image
```html
<div class="panel">
    <img src="image.jpg" class="panel-image" alt="Card image">
    <div class="panel-body panel-body-with-image">
        Content below image
    </div>
</div>
```

**Visual:** Image at top with rounded corners, 200px height

### Card with Form
```html
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">Form Title</h3>
    </div>
    <div class="panel-body">
        <form>
            <div class="form-group">...</div>
            <div class="form-group">...</div>
        </form>
    </div>
    <div class="panel-footer">
        <button class="btn btn-primary">Save</button>
        <button class="btn btn-default">Cancel</button>
    </div>
</div>
```

**Visual:** Proper spacing, footer with action buttons

### Card with Table
```html
<div class="panel">
    <div class="panel-heading">
        <h3 class="panel-title">Data Table</h3>
    </div>
    <div class="panel-body">
        <table class="table">...</table>
    </div>
</div>
```

**Visual:** Table margins removed, seamless integration

## Design Specifications

### Spacing
- Header padding: `16px 24px`
- Body padding: `20px 24px`
- Footer padding: `16px 24px`
- Card margin-bottom: `24px`
- Grid gap: `24px`

### Typography
- Title font-size: `16px`
- Title font-weight: `600`
- Body font-size: `14px`
- Metric number: `28px` (info-box), `32px` (metric-card), `36px` (small-box)

### Colors
- Background: `#ffffff`
- Border: `#f0f0f0`
- Shadow: `0 2px 8px rgba(0, 0, 0, 0.08)`
- Hover shadow: `0 4px 12px rgba(0, 0, 0, 0.12)`

### Border Radius
- Card: `8px`
- Icon containers: `8px`
- Badges: `12px`

### Transitions
- Duration: `0.3s`
- Easing: `cubic-bezier(0.645, 0.045, 0.355, 1)`

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- CSS Grid with fallbacks
- Flexbox alternative layouts

## Accessibility
- Proper color contrast (WCAG AA)
- Semantic HTML structure
- Focus states visible
- Screen reader compatible
- Print styles included

## Testing
Open `test-cards.html` in a browser to see all card variants in action.
