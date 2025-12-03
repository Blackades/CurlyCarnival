# Search and Filter Controls - Integration Guide

## How to Use in PHPNuxBill Templates

### 1. Router List Page Integration

**File:** `phpnuxbill-fresh/ui/ui/admin/routers/list.tpl`

Add search and filter bar before the table:

```html
<div class="filter-bar">
    <div class="search-input-wrapper">
        <input type="search" class="form-control" id="router-search" 
               placeholder="Search routers...">
    </div>
    
    <div class="filter-group">
        <span class="filter-label">Status:</span>
        <select class="filter-select" id="status-filter">
            <option value="">All Status</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
        </select>
    </div>
    
    <div class="filter-actions">
        <button class="btn btn-primary btn-sm" onclick="applyFilters()">
            <i class="fa fa-filter"></i> Apply
        </button>
    </div>
</div>
```

### 2. Customer List Page Integration

**File:** `phpnuxbill-fresh/ui/ui/admin/customers/list.tpl`

```html
<div class="filters-horizontal">
    <div class="filter-item">
        <div class="search-wrapper">
            <input type="text" class="form-control" 
                   placeholder="Search customers...">
        </div>
    </div>
    
    <div class="filter-item">
        <span class="filter-label">Status:</span>
        <select class="filter-dropdown">
            <option value="">All</option>
            <option value="active">Active</option>
        </select>
    </div>
</div>
```

### 3. VPN Logs Filtering

**File:** `phpnuxbill-fresh/ui/ui/admin/routers/vpn-logs.tpl`

```html
<div class="filter-bar">
    <div class="date-range-filter">
        <input type="date" class="form-control" id="date-from">
        <span class="date-range-separator">to</span>
        <input type="date" class="form-control" id="date-to">
    </div>
    
    <div class="filter-group">
        <select class="filter-select">
            <option>All Events</option>
            <option>Connections</option>
            <option>Disconnections</option>
        </select>
    </div>
</div>
```

### 4. Active Filters Display

Show applied filters with remove buttons:

```html
<div class="filter-tags">
    <span class="filter-tags-label">Active Filters:</span>
    
    {foreach $activeFilters as $filter}
    <div class="filter-tag">
        <span class="filter-tag-label">{$filter.label}:</span>
        <span class="filter-tag-value">{$filter.value}</span>
        <button class="filter-tag-close" 
                onclick="removeFilter('{$filter.key}')">×</button>
    </div>
    {/foreach}
    
    <button class="clear-all-filters" onclick="clearAllFilters()">
        Clear All
    </button>
</div>
```

## JavaScript Integration

### Basic Filter Functionality

```javascript
// Apply filters
function applyFilters() {
    const search = document.getElementById('router-search').value;
    const status = document.getElementById('status-filter').value;
    
    // Reload table with filters
    window.location.href = '?search=' + search + '&status=' + status;
}

// Remove individual filter
function removeFilter(key) {
    // Remove from URL and reload
    const url = new URL(window.location);
    url.searchParams.delete(key);
    window.location.href = url.toString();
}

// Clear all filters
function clearAllFilters() {
    window.location.href = window.location.pathname;
}
```

### DataTables Integration

The styles automatically work with DataTables:

```javascript
$('#router-table').DataTable({
    // DataTables will use the styled search and length controls
    dom: 'lfrtip',
    // ... other options
});
```

## CSS Classes Reference

### Search Inputs
- `.search-wrapper` - Container with icon
- `.search-input-wrapper` - Alternative container
- `.search-input` - Direct styling

### Filter Controls
- `.filter-select` - Dropdown filter
- `.filter-dropdown` - Alternative dropdown
- `.filter-label` - Filter label text
- `.filter-container` - Filter + label wrapper

### Layouts
- `.filter-bar` - Main filter container
- `.filters-horizontal` - Horizontal layout
- `.filters-vertical` - Vertical layout
- `.filter-group` - Group of related filters

### Filter Tags
- `.filter-tags` - Tags container
- `.filter-tag` - Individual tag
- `.filter-tag-close` - Close button
- `.clear-all-filters` - Clear all button

## Best Practices

1. **Always wrap search inputs** in `.search-wrapper` or `.search-input-wrapper`
2. **Use filter-bar** for complex filter interfaces
3. **Show active filters** using filter tags for better UX
4. **Include clear/reset** buttons for easy filter removal
5. **Make filters responsive** - they stack automatically on mobile
6. **Use loading states** when filters trigger async operations

## Testing Checklist

- [ ] Search input displays icon correctly
- [ ] Filter dropdowns have proper height and styling
- [ ] Focus states show blue border and shadow
- [ ] Hover states work on all controls
- [ ] Filter tags can be removed individually
- [ ] Clear all button removes all tags
- [ ] Layout is responsive on mobile
- [ ] Works with DataTables if used
- [ ] Loading states display correctly
