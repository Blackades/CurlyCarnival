# ARIA Quick Reference Card

## Icon-Only Buttons
```html
<button aria-label="Edit item">
    <i class="fa fa-edit" aria-hidden="true"></i>
</button>
```

## Buttons with Icon and Text
```html
<button>
    <i class="fa fa-save" aria-hidden="true"></i>
    Save Changes
</button>
```

## Form Field with Error
```html
<input type="email" id="email" 
       aria-invalid="true" 
       aria-describedby="email-error">
<div id="email-error" role="alert" aria-live="polite">
    Please enter a valid email address
</div>
```

## Required Field
```html
<input type="text" required 
       aria-required="true" 
       aria-describedby="field-help">
<small id="field-help">This field is required</small>
```

## Success Alert
```html
<div class="alert alert-success" role="alert" aria-live="polite">
    <i class="fa fa-check-circle" aria-hidden="true"></i>
    Success! Your changes have been saved.
</div>
```

## Error Alert
```html
<div class="alert alert-danger" role="alert" aria-live="assertive">
    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
    Error! Unable to process your request.
</div>
```

## Loading Button
```html
<button aria-busy="true" aria-live="polite">
    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
    <span class="sr-only">Loading...</span>
    Processing
</button>
```

## Modal Dialog
```html
<div class="modal" role="dialog" 
     aria-labelledby="modalTitle" 
     aria-hidden="true" 
     aria-modal="true">
    <h5 id="modalTitle">Confirm Action</h5>
    <button aria-label="Close dialog">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
```

## Dropdown Menu
```html
<button aria-haspopup="true" aria-expanded="false">
    Options
</button>
<ul role="menu" aria-label="Options menu">
    <li role="menuitem"><a href="#">Action</a></li>
</ul>
```

## Data Table
```html
<table>
    <caption class="sr-only">Customer list</caption>
    <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
        </tr>
    </thead>
</table>
```

## Navigation
```html
<nav aria-label="Main navigation">
    <a href="#" aria-current="page">Current Page</a>
</nav>
```

## Status Region
```html
<div role="status" aria-live="polite" aria-atomic="true">
    Uploading file... 45% complete
</div>
```
