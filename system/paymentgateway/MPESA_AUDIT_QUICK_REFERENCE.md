# M-Pesa Audit Quick Reference

## Database Fields

| Field | M-Pesa Data | Example |
|-------|-------------|---------|
| `gateway_trx_id` | CheckoutRequestID | `ws_CO_191220191020363925` |
| `pg_request` | STK Push request JSON | `{"BusinessShortCode":"174379",...}` |
| `pg_paid_response` | Callback response JSON | `{"ResultCode":0,"MpesaReceiptNumber":"NLJ7RT61SV",...}` |
| `payment_method` | Payment method | `M-PESA` |
| `payment_channel` | Payment channel | `mpesa_stk` |
| `status` | Transaction status | `1=pending, 2=paid, 4=failed` |

## URLs

| Action | URL | Method |
|--------|-----|--------|
| List transactions | `/paymentgateway/audit/mpesa` | GET |
| View transaction | `/paymentgateway/auditview/{id}` | GET |
| Search transactions | `/paymentgateway/audit/mpesa?q={query}` | GET |

## Search Fields

- `gateway_trx_id` (CheckoutRequestID)
- `username`
- `routers`
- `plan_name`

## Key M-Pesa Fields

### CheckoutRequestID
- **Stored in:** `gateway_trx_id`
- **Format:** `ws_CO_DDMMYYYYHHMMSSXXXXXXXXX`
- **Purpose:** Unique transaction identifier

### MpesaReceiptNumber
- **Stored in:** `pg_paid_response` → `CallbackMetadata` → `Item[]`
- **Format:** Alphanumeric (e.g., `NLJ7RT61SV`)
- **Purpose:** Official payment receipt

## Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 1 | UNPAID | Pending payment |
| 2 | PAID | Payment successful |
| 3 | FAILED | Payment failed |
| 4 | CANCELED | Payment cancelled |

## M-Pesa Result Codes

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Insufficient balance |
| 1032 | User cancelled |
| 1037 | Timeout |
| 2001 | Invalid initiator |

## Data Storage Functions

### Transaction Creation
```php
mpesa_create_transaction($trx, $user)
// Stores: gateway_trx_id, pg_request, expired_date
```

### Payment Notification
```php
mpesa_payment_notification()
// Stores: pg_paid_response, payment_method, payment_channel, paid_date, status
```

### Status Query
```php
mpesa_get_status($trx, $user)
// Updates: pg_paid_response, status, paid_date
```

## Verification Commands

```bash
# Run verification script
php phpnuxbill-fresh/system/verify_mpesa_audit.php

# Check webhook logs
cat phpnuxbill-fresh/pages/mpesa-webhook.html

# Check system logs
# Look for entries with 'M-Pesa' tag
```

## Common Queries

### Find transaction by CheckoutRequestID
```sql
SELECT * FROM tbl_payment_gateway 
WHERE gateway_trx_id = 'ws_CO_191220191020363925';
```

### Find all pending M-Pesa transactions
```sql
SELECT * FROM tbl_payment_gateway 
WHERE gateway = 'mpesa' AND status = 1;
```

### Find all paid M-Pesa transactions today
```sql
SELECT * FROM tbl_payment_gateway 
WHERE gateway = 'mpesa' 
AND status = 2 
AND DATE(paid_date) = CURDATE();
```

### Extract MpesaReceiptNumber from response
```sql
SELECT id, username, 
JSON_EXTRACT(pg_paid_response, '$.CallbackMetadata.Item[1].Value') as receipt
FROM tbl_payment_gateway 
WHERE gateway = 'mpesa' AND status = 2;
```

## Troubleshooting

### Missing pg_request
- Check if STK Push was sent successfully
- Review system logs for errors
- Verify M-Pesa credentials

### Missing pg_paid_response
- Check callback logs at `pages/mpesa-webhook.html`
- Verify callback URL is registered with M-Pesa
- Check firewall/walled garden settings

### Missing MpesaReceiptNumber
- Verify ResultCode = 0 in pg_paid_response
- Check CallbackMetadata structure
- Review raw callback data

## Quick Checks

✓ **Transaction created:** Check if record exists in `tbl_payment_gateway`  
✓ **STK Push sent:** Check if `pg_request` is not empty  
✓ **Callback received:** Check if `pg_paid_response` is not empty  
✓ **Payment successful:** Check if `status = 2` and MpesaReceiptNumber exists  
✓ **Package activated:** Check if `paid_date` is set and user has active service  

## Support Resources

- **Audit Guide:** `MPESA_AUDIT_GUIDE.md`
- **Webhook Logs:** `pages/mpesa-webhook.html`
- **System Logs:** Check logs with 'M-Pesa' tag
- **Daraja API Docs:** https://developer.safaricom.co.ke/
