# Search and Filter Controls - Visual Reference

## Quick Visual Guide

### Search Input Fields
```
┌─────────────────────────────────────┐
│ 🔍  Search routers...              │  ← Icon inside, 36px height
└─────────────────────────────────────┘
   ↑                                  ↑
   12px                              12px
   from left                         padding
```

### Filter Dropdowns
```
┌─────────────────────────┐
│ All Status          ▼   │  ← 36px height, 120px min-width
└─────────────────────────┘
```

### Filter Bar Layout
```
┌────────────────────────────────────────────────────────────┐
│  🔍 Search...   Status: [All ▼]   Type: [All ▼]  [Apply]  │
└────────────────────────────────────────────────────────────┘
```

### Active Filter Tags
```
Status: Active ×    Type: MikroTik ×    [Clear All]
```

## Color Specifications
- Border: #d9d9d9
- Focus Border: #1890ff
- Hover Border: #40a9ff
- Background: #ffffff
- Icon Color: #8c8c8c
- Tag Background: #f5f5f5

## Spacing
- Gap between filters: 16px
- Input padding: 8px 12px
- Tag padding: 4px 8px
- Filter bar padding: 16px

## Interactive States
- **Default:** Gray border
- **Hover:** Light blue border
- **Focus:** Blue border + shadow
- **Loading:** Animated spinner
- **Disabled:** Gray background, 60% opacity
