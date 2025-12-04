# Design Document

## Overview

This design addresses the "Failed to load configuration" error in the M-Pesa STK Push payment gateway by implementing graceful handling of missing or invalid configuration data. The solution separates configuration retrieval logic for display operations (which should be permissive) from payment operations (which should be strict).

## Architecture

The fix involves modifying the configuration retrieval pattern in the `mpesastk.php` file to support two modes of operation:

1. **Permissive Mode**: Used when displaying the configuration form - returns empty defaults if no configuration exists
2. **Strict Mode**: Used during payment processing - throws exceptions if configuration is missing or invalid

### Current Flow (Problematic)

```
Admin accesses config page
    ↓
mpesastk_show_config() called
    ↓
mpesastk_get_config() called
    ↓
No config found → Exception thrown
    ↓
Caught in show_config → Redirect with error
    ↓
User cannot access form
```

### Proposed Flow (Fixed)

```
Admin accesses config page
    ↓
mpesastk_show_config() called
    ↓
mpesastk_get_config($strict = false) called
    ↓
No config found → Return empty defaults
    ↓
Form displays with empty fields
    ↓
Admin can save configuration
```

## Components and Interfaces

### Modified Functions

#### 1. `mpesastk_get_config($strict = true)`

**Purpose**: Retrieve M-Pesa configuration with optional strict mode

**Parameters**:
- `$strict` (boolean, default: true): When true, throws exceptions for missing config. When false, returns defaults.

**Returns**: Array of configuration values

**Behavior**:
- In strict mode (default): Maintains current behavior for payment operations
- In permissive mode: Returns array with empty string defaults for all config keys

**Default Configuration Structure**:
```php
[
    'consumer_key' => '',
    'consumer_secret' => '',
    'business_shortcode' => '',
    'passkey' => '',
    'environment' => 'sandbox',
    'account_reference' => 'PHPNuxBill',
    'transaction_desc' => 'Payment for Internet Access'
]
```

#### 2. `mpesastk_show_config()`

**Changes**:
- Call `mpesastk_get_config(false)` instead of `mpesastk_get_config()`
- Remove try-catch block since no exceptions will be thrown
- Simplify error handling

#### 3. Payment Processing Functions

**No Changes Required**:
- `mpesastk_initiate_stk_push()`: Already calls `mpesastk_get_config()` with default strict mode
- `mpesastk_get_token()`: Already calls `mpesastk_get_config()` with default strict mode
- `mpesastk_check_status()`: Already calls `mpesastk_get_config()` with default strict mode

## Data Models

### Configuration Record (tbl_appconfig)

```
setting: 'mpesastk_config' (varchar)
value: JSON string containing configuration (text)
```

**JSON Structure**:
```json
{
    "consumer_key": "string",
    "consumer_secret": "string",
    "business_shortcode": "string",
    "passkey": "string",
    "environment": "sandbox|production",
    "account_reference": "string",
    "transaction_desc": "string"
}
```

## Error Handling

### Configuration Display (Permissive)

- Missing configuration → Return defaults, no error
- Invalid JSON → Log warning, return defaults
- Database error → Log error, return defaults

### Payment Processing (Strict)

- Missing configuration → Throw exception with clear message
- Invalid JSON → Throw exception with clear message
- Database error → Throw exception with clear message

### Logging Strategy

All configuration errors should be logged using the existing `_log()` function:

- **Display errors**: Log as 'MPESA-CONFIG' type with 'warning' level
- **Payment errors**: Log as 'MPESA-ERROR' type with 'error' level

## Testing Strategy

### Manual Testing

1. **First-time configuration access**:
   - Delete any existing mpesastk_config from tbl_appconfig
   - Navigate to payment gateway configuration page
   - Verify form displays with empty fields
   - Fill in configuration and save
   - Verify configuration is saved correctly

2. **Existing configuration access**:
   - Ensure mpesastk_config exists in database
   - Navigate to configuration page
   - Verify form displays with saved values

3. **Payment processing without configuration**:
   - Delete mpesastk_config from database
   - Attempt to process a payment
   - Verify appropriate error message is displayed

4. **Corrupted configuration handling**:
   - Manually corrupt the JSON in tbl_appconfig.value
   - Access configuration page
   - Verify form displays with defaults
   - Attempt payment processing
   - Verify error is logged and displayed

### Edge Cases

1. **Empty database table**: System should create new record on first save
2. **Partial configuration**: Missing fields should use defaults
3. **Extra fields in JSON**: Should be ignored gracefully
4. **Database connection failure**: Should log error and fail gracefully

## Implementation Notes

### Backward Compatibility

- Default parameter value ensures existing code continues to work
- No changes to database schema required
- No changes to template files required
- Existing payment processing logic remains unchanged

### Performance Considerations

- Static caching in `mpesastk_get_config()` should be maintained
- Cache should be keyed by strict mode to avoid conflicts
- No additional database queries introduced

### Security Considerations

- Configuration values should not be exposed in error messages
- Logging should not include sensitive credentials
- Form validation remains unchanged
