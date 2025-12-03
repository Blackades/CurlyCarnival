# Table Styles Visual Reference

## Quick Reference Guide for Task 5 Implementation

### Table Header Styling
```
┌─────────────────────────────────────────────────────────┐
│ #  │ Router Name    │ IP Address  │ Status  │ Actions  │ ← Headers
├────┼────────────────┼─────────────┼─────────┼──────────┤
│    │                │             │         │          │
└─────────────────────────────────────────────────────────┘

Properties:
- Background: #fafafa (subtle gray)
- Font Weight: 600 (semibold)
- Padding: 12px 16px
- Border Bottom: 1px solid #f0f0f0
```

### Table Row Styling (Striped)
```
┌─────────────────────────────────────────────────────────┐
│ 1  │ MikroTik-Main  │ 192.168.1.1 │ Online  │ [Edit]   │ ← White
├────┼────────────────┼─────────────┼─────────┼──────────┤
│ 2  │ MikroTik-Branch│ 192.168.2.1 │ Online  │ [Edit]   │ ← Gray (#fafafa)
├────┼────────────────┼─────────────┼─────────┼──────────┤
│ 3  │ MikroTik-Remote│ 192.168.3.1 │ Warning │ [Edit]   │ ← White
└─────────────────────────────────────────────────────────┘

Properties:
- Odd Rows: #ffffff (white)
- Even Rows: #fafafa (light gray)
- Hover: #f5f5f5 (slightly darker gray)
- Padding: 12px 16px
- Border: 1px solid #f0f0f0
```

### Action Buttons Layout
```
┌──────────────────────────────────────┐
│ Actions Column                       │
├──────────────────────────────────────┤
│  [👁] [✏️] [🗑️]  ← 4px gaps         │
│   │    │    │                        │
│   │    │    └─ Delete (Red hover)    │
│   │    └────── Edit (Blue hover)     │
│   └─────────── View (Cyan hover)     │
└──────────────────────────────────────┘

Button Specs:
- Size: 28px × 28px
- Border Radius: 4px
- Gap: 4px between buttons
- Hover: Background color + translateY(-1px)

Colors:
- View:   #13c2c2 (info)
- Edit:   #1890ff (primary)
- Delete: #f5222d (danger)
```

### Empty Table State
```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│                        📥                               │
│                    (48px icon)                          │
│                                                         │
│                  No data available                      │
│                     (14px text)                         │
│                                                         │
│            There are no items to display                │
│                  (12px subtext)                         │
│                                                         │
└─────────────────────────────────────────────────────────┘

Properties:
- Padding: 40px 20px
- Text Align: center
- Icon Size: 48px
- Icon Color: #bfbfbf (50% opacity)
- Text Color: #8c8c8c (tertiary)
- Subtext Color: #bfbfbf (disabled)
```

### Status Indicators
```
● Online    ← Green dot (8px) + text
● Warning   ← Orange dot (8px) + text
● Offline   ← Red dot (8px) + text

Badge Styles:
[Active]    ← Green background
[Pending]   ← Orange background
[Suspended] ← Red background
```

### Table Card Wrapper
```
┌─────────────────────────────────────────────────────────┐
│ Table Toolbar                                           │
│ ┌─────────────┐                    [Search] [+ Add]    │
│ │ Table Title │                                         │
│ └─────────────┘                                         │
├─────────────────────────────────────────────────────────┤
│ Table Headers                                           │
├─────────────────────────────────────────────────────────┤
│ Table Rows                                              │
│ ...                                                     │
├─────────────────────────────────────────────────────────┤
│ Showing 1-10 of 50        [Prev] [1] [2] [Next]       │
│ Table Footer                                            │
└─────────────────────────────────────────────────────────┘

Properties:
- Border Radius: 8px
- Box Shadow: 0 2px 8px rgba(0,0,0,0.08)
- Background: white
- Toolbar Padding: 16px
- Footer Padding: 16px
```

### Responsive Behavior

#### Desktop (>768px)
```
┌──────────────────────────────────────────────────────────┐
│ # │ Name │ IP │ Location │ Status │ VPN │ Date │ Actions│
├───┼──────┼────┼──────────┼────────┼─────┼──────┼────────┤
│ 1 │ ...  │... │   ...    │  ...   │ ... │ ...  │ [...]  │
└──────────────────────────────────────────────────────────┘
All columns visible
```

#### Tablet (768px)
```
┌────────────────────────────────────────────────┐
│ ← Scroll horizontally →                        │
│ # │ Name │ IP │ Location │ Status │ VPN │ ... │
├───┼──────┼────┼──────────┼────────┼─────┼─────┤
│ 1 │ ...  │... │   ...    │  ...   │ ... │ ... │
└────────────────────────────────────────────────┘
Horizontal scroll enabled
```

#### Mobile (<768px)
```
┌──────────────────────────┐
│ # │ Name │ Status │ Act. │
├───┼──────┼────────┼──────┤
│ 1 │ ... │  ...   │ [...] │
└──────────────────────────┘
Less important columns hidden
Reduced padding (8px 10px)
Smaller font (12px)
```

## CSS Class Quick Reference

### Basic Table Classes
- `.table` - Base table styling
- `.table-striped` - Alternating row colors
- `.table-hover` - Row hover effects
- `.table-bordered` - All borders visible
- `.table-condensed` - Reduced padding

### Table Components
- `.table-card` - Modern card wrapper
- `.table-toolbar` - Top toolbar section
- `.table-footer` - Bottom footer section
- `.table-responsive` - Responsive wrapper
- `.table-actions` - Action button container

### Action Buttons
- `.btn-icon` - Icon-only button (28px)
- `.btn-view` - View action (cyan)
- `.btn-edit` - Edit action (blue)
- `.btn-delete` - Delete action (red)

### Empty State
- `.table-empty-state` - Container
- `.table-empty-state-icon` - Large icon
- `.table-empty-state-text` - Main message
- `.table-empty-state-subtext` - Secondary text

### Status Indicators
- `.status-indicator` - Dot + text container
- `.status-dot` - 8px colored dot
- `.status-dot-success` - Green dot
- `.status-dot-warning` - Orange dot
- `.status-dot-danger` - Red dot

### Column Types
- `.checkbox-column` - 40px width
- `.number-column` - 60px width
- `.actions-column` - 120px width
- `.status-column` - 100px width
- `.date-column` - 140px width

## Color Palette

### Backgrounds
- Header: `#fafafa`
- Even Rows: `#fafafa`
- Odd Rows: `#ffffff`
- Hover: `#f5f5f5`

### Borders
- Light: `#f0f0f0`
- Standard: `#d9d9d9`

### Text
- Primary: `#262626`
- Secondary: `#595959`
- Tertiary: `#8c8c8c`
- Disabled: `#bfbfbf`

### Actions
- View: `#13c2c2`
- Edit: `#1890ff`
- Delete: `#f5222d`

### Status
- Success: `#52c41a`
- Warning: `#faad14`
- Danger: `#f5222d`
- Info: `#13c2c2`

## Spacing Scale
- XS: 4px
- SM: 8px
- MD: 12px
- LG: 16px
- XL: 20px
- XXL: 24px

## Common Patterns

### Router List Table
```html
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Router Name</th>
            <th>IP Address</th>
            <th>Status</th>
            <th class="actions-column">Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>MikroTik-Main</td>
            <td>192.168.1.1</td>
            <td>
                <div class="status-indicator">
                    <span class="status-dot status-dot-success"></span>
                    <span>Online</span>
                </div>
            </td>
            <td class="actions-column">
                <div class="table-actions">
                    <button class="btn btn-icon btn-view">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-icon btn-edit">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-icon btn-delete">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    </tbody>
</table>
```

### Customer List Table
```html
<table class="table table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Customer Name</th>
            <th>Email</th>
            <th>Plan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>John Doe</td>
            <td>john@example.com</td>
            <td>Premium 100Mbps</td>
            <td><span class="label label-success">Active</span></td>
        </tr>
    </tbody>
</table>
```

### Empty Table
```html
<table class="table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <div class="table-empty-state">
                    <div class="table-empty-state-icon">
                        <i class="fa fa-inbox"></i>
                    </div>
                    <div class="table-empty-state-text">
                        No data available
                    </div>
                    <div class="table-empty-state-subtext">
                        There are no items to display
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
```

## Browser Support
✓ Chrome 90+
✓ Firefox 88+
✓ Safari 14+
✓ Edge 90+
✓ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance Notes
- All transitions use GPU-accelerated properties
- Efficient CSS selectors (max 3 levels)
- No JavaScript required
- Print-friendly styles included
- Minimal repaints/reflows

---
**Reference**: Task 5 - Modernize data tables
**File**: phpnuxbill-modern.css (lines 2541-3150+)
**Test File**: test-tables.html
