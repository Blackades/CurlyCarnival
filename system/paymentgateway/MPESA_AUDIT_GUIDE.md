# M-Pesa Audit and Transaction Tracking Guide

## Overview

This guide explains how the M-Pesa payment gateway integrates with PHPNuxBill's audit and transaction tracking system. The audit functionality allows administrators to view, search, and analyze M-Pesa payment transactions.

## Key Features

### 1. Transaction Data Storage

All M-Pesa transactions are stored in the `tbl_payment_gateway` table with the following key fields:

| Field | Purpose | M-Pesa Data |
|-------|---------|-------------|
| `gateway_trx_id` | Unique transaction identifier | CheckoutRequestID from M-Pesa |
| `pg_request` | Request payload (JSON) | STK Push request data |
| `pg_paid_response` | Response payload (JSON) | M-Pesa callback response |
| `payment_method` | Payment method name | "M-PESA" |
| `payment_channel` | Payment channel | "mpesa_stk" |
| `status` | Transaction status | 1=pending, 2=paid, 3=expired, 4=failed |

### 2. STK Push Request Data (pg_request)

The `pg_request` field stores the complete STK Push request sent to M-Pesa:

```json
{
  "BusinessShortCode": "174379",
  "Password": "base64_encoded_password",
  "Timestamp": "20241203123456",
  "TransactionType": "CustomerPayBillOnline",
  "Amount": 1000,
  "PartyA": "254712345678",
  "PartyB": "174379",
  "PhoneNumber": "254712345678",
  "CallBackURL": "https://example.com/callback/mpesa",
  "AccountReference": "username",
  "TransactionDesc": "Plan Name"
}
```

**Key Fields:**
- `BusinessShortCode`: Your M-Pesa business number
- `Amount`: Transaction amount in KES (integer, no decimals)
- `PhoneNumber`: Customer's phone number in E.164 format (254XXXXXXXXX)
- `AccountReference`: Customer username
- `TransactionDesc`: Plan/package name

### 3. M-Pesa Callback Response Data (pg_paid_response)

#### Successful Payment Response

```json
{
  "MerchantRequestID": "29115-34620561-1",
  "CheckoutRequestID": "ws_CO_191220191020363925",
  "ResultCode": 0,
  "ResultDesc": "The service request is processed successfully.",
  "CallbackMetadata": {
    "Item": [
      {"Name": "Amount", "Value": 1000},
      {"Name": "MpesaReceiptNumber", "Value": "NLJ7RT61SV"},
      {"Name": "TransactionDate", "Value": 20191219102115},
      {"Name": "PhoneNumber", "Value": 254708374149}
    ]
  }
}
```

**Key Fields:**
- `CheckoutRequestID`: Unique transaction identifier (matches gateway_trx_id)
- `MerchantRequestID`: M-Pesa merchant request ID
- `ResultCode`: 0 = success, other = failure
- `MpesaReceiptNumber`: Official M-Pesa receipt number (proof of payment)
- `TransactionDate`: Payment completion timestamp (YYYYMMDDHHmmss format)

#### Failed/Cancelled Payment Response

```json
{
  "MerchantRequestID": "29115-34620561-2",
  "CheckoutRequestID": "ws_CO_191220191020363926",
  "ResultCode": 1032,
  "ResultDesc": "Request cancelled by user"
}
```

**Common Result Codes:**
- `0`: Success
- `1`: Insufficient balance
- `1032`: User cancelled
- `1037`: Timeout (user didn't respond)
- `2001`: Invalid initiator information

## Accessing Audit Features

### 1. View All M-Pesa Transactions

**URL:** `/paymentgateway/audit/mpesa`

**Features:**
- Lists all M-Pesa transactions in reverse chronological order
- Shows transaction status with color coding:
  - Yellow: Pending (status 1)
  - Green: Paid (status 2)
  - Red: Failed/Cancelled (status 4)
- Displays key information:
  - Transaction ID
  - CheckoutRequestID (gateway_trx_id)
  - Customer username
  - Plan name
  - Amount
  - Payment channel
  - Timestamps (created, expired, paid)
  - Status

### 2. Search Transactions

The audit page includes a search box that searches across multiple fields:

**Searchable Fields:**
- `gateway_trx_id` (CheckoutRequestID)
- `username` (customer username)
- `routers` (router name)
- `plan_name` (package/plan name)

**Example Searches:**
- Search by CheckoutRequestID: `ws_CO_191220191020363925`
- Search by username: `john_doe`
- Search by plan: `Premium Package`
- Search by router: `Router1`

### 3. View Transaction Details

**URL:** `/paymentgateway/auditview/{transaction_id}`

**Features:**
- Displays complete transaction information
- Shows all fields from `pg_request` in a table format
- Shows all fields from `pg_paid_response` in a table format
- Includes links to:
  - Customer profile
  - Invoice/activation record
  - Payment link (if applicable)

**Transaction Details Displayed:**
- Transaction ID
- Invoice number
- Status
- Customer username
- Plan name
- Router
- Price
- Payment method and channel
- Created date
- Expired date
- Paid date

**Request Data Table:**
All fields from the STK Push request are displayed, including:
- BusinessShortCode
- Timestamp
- TransactionType
- Amount
- PhoneNumber
- AccountReference
- TransactionDesc

**Response Data Table:**
All fields from the M-Pesa callback are displayed, including:
- CheckoutRequestID
- MerchantRequestID
- ResultCode
- ResultDesc
- CallbackMetadata (if successful):
  - Amount
  - MpesaReceiptNumber
  - TransactionDate
  - PhoneNumber

## Important M-Pesa Fields

### CheckoutRequestID

- **Location:** `gateway_trx_id` field
- **Purpose:** Unique identifier for each STK Push transaction
- **Format:** `ws_CO_DDMMYYYYHHMMSSXXXXXXXXX`
- **Usage:** 
  - Used to track transaction status
  - Used in status query API calls
  - Searchable in audit interface
  - Links transaction request to callback response

### MpesaReceiptNumber

- **Location:** `pg_paid_response` → `CallbackMetadata` → `Item` array
- **Purpose:** Official M-Pesa receipt number (proof of payment)
- **Format:** Alphanumeric string (e.g., "NLJ7RT61SV")
- **Usage:**
  - Proof of payment for customer
  - Used for reconciliation
  - Stored in transaction record
  - Displayed in audit view

## Transaction Status Flow

### Status Codes

| Status | Description | Color |
|--------|-------------|-------|
| 1 | UNPAID (Pending) | Yellow |
| 2 | PAID (Success) | Green |
| 3 | FAILED | Red |
| 4 | CANCELED | Red |

### Status Transitions

```
1. Transaction Created
   ↓
   Status: 1 (UNPAID)
   ↓
2. STK Push Sent
   ↓
   Customer receives prompt on phone
   ↓
3a. Customer Enters PIN → Payment Success
    ↓
    Callback received (ResultCode = 0)
    ↓
    Status: 2 (PAID)
    ↓
    Package activated

3b. Customer Cancels → Payment Cancelled
    ↓
    Callback received (ResultCode = 1032)
    ↓
    Status: 4 (CANCELED)

3c. Timeout → Payment Expired
    ↓
    Callback received (ResultCode = 1037)
    ↓
    Status: 4 (FAILED)

3d. Insufficient Balance
    ↓
    Callback received (ResultCode = 1)
    ↓
    Status: 4 (FAILED)
```

## Data Verification

### Verifying Transaction Data

1. **Check Request Data:**
   - Navigate to `/paymentgateway/auditview/{transaction_id}`
   - Scroll to "Response when request payment" section
   - Verify all STK Push request fields are present
   - Confirm phone number is in E.164 format (254XXXXXXXXX)
   - Verify amount matches transaction price

2. **Check Response Data:**
   - Scroll to "Response when payment PAID" section
   - For successful payments, verify:
     - ResultCode = 0
     - MpesaReceiptNumber is present
     - Amount matches transaction price
     - TransactionDate is present
   - For failed payments, verify:
     - ResultCode ≠ 0
     - ResultDesc explains failure reason

3. **Check Status Consistency:**
   - If ResultCode = 0, status should be 2 (PAID)
   - If ResultCode ≠ 0, status should be 4 (FAILED/CANCELED)
   - Paid transactions should have paid_date set
   - Failed transactions should not have paid_date

## Troubleshooting

### Missing Transaction Data

**Problem:** Transaction exists but pg_request or pg_paid_response is empty

**Possible Causes:**
1. Transaction created but STK Push failed before sending
2. Callback not received from M-Pesa
3. Callback received but processing failed

**Solution:**
1. Check system logs for M-Pesa errors
2. Use manual status query feature
3. Check M-Pesa webhook logs at `pages/mpesa-webhook.html`

### CheckoutRequestID Not Found

**Problem:** Search by CheckoutRequestID returns no results

**Possible Causes:**
1. Transaction not yet created
2. STK Push failed before CheckoutRequestID was assigned
3. Incorrect CheckoutRequestID format

**Solution:**
1. Search by username instead
2. Check if transaction exists with status 1 (pending)
3. Verify CheckoutRequestID format (should start with "ws_CO_")

### MpesaReceiptNumber Missing

**Problem:** Successful payment but no MpesaReceiptNumber in audit view

**Possible Causes:**
1. Callback received but CallbackMetadata missing
2. M-Pesa API issue
3. Data parsing error

**Solution:**
1. Check raw callback data in `pages/mpesa-webhook.html`
2. Verify callback structure matches expected format
3. Check system logs for parsing errors

## Best Practices

### 1. Regular Audit Reviews

- Review M-Pesa transactions daily
- Check for pending transactions older than 2 minutes
- Verify all paid transactions have MpesaReceiptNumber
- Monitor failed transaction patterns

### 2. Search Optimization

- Use CheckoutRequestID for specific transaction lookup
- Use username for customer-specific searches
- Use date range filters (if available) for period analysis
- Export data for external analysis if needed

### 3. Data Retention

- Keep transaction records for at least 12 months
- Archive old transactions periodically
- Maintain backup of pg_request and pg_paid_response data
- Store MpesaReceiptNumbers for reconciliation

### 4. Reconciliation

- Match MpesaReceiptNumbers with M-Pesa statements
- Verify amounts match between system and M-Pesa
- Check for duplicate payments
- Identify missing callbacks

## API Integration Points

### 1. Transaction Creation

**Function:** `mpesa_create_transaction($trx, $user)`

**Data Stored:**
- `gateway_trx_id`: CheckoutRequestID from M-Pesa response
- `pg_request`: Complete STK Push request JSON
- `expired_date`: Current time + 2 minutes

### 2. Payment Notification

**Function:** `mpesa_payment_notification()`

**Data Stored:**
- `pg_paid_response`: Complete callback JSON
- `payment_method`: "M-PESA"
- `payment_channel`: "mpesa_stk"
- `paid_date`: Transaction completion timestamp
- `status`: 2 (paid) or 4 (failed)

### 3. Status Query

**Function:** `mpesa_get_status($trx, $user)`

**Data Updated:**
- `pg_paid_response`: Status query response JSON
- `status`: Updated based on query result
- `paid_date`: Set if payment confirmed

## Security Considerations

### 1. Data Protection

- All transaction data is stored in database
- Sensitive fields (passwords, tokens) are not stored
- Access restricted to admin users only
- Audit logs track all access

### 2. PII Handling

- Phone numbers stored in E.164 format
- Customer usernames linked to transactions
- MpesaReceiptNumbers are non-sensitive
- No M-Pesa PINs or passwords stored

### 3. Audit Trail

- All M-Pesa API calls logged
- Callback data logged to file
- Transaction status changes tracked
- Admin actions logged

## Conclusion

The M-Pesa audit and transaction tracking system provides comprehensive visibility into all payment transactions. By properly utilizing the audit features, administrators can:

- Monitor payment success rates
- Troubleshoot payment issues
- Reconcile payments with M-Pesa statements
- Provide customer support
- Analyze payment patterns
- Ensure data integrity

For additional support, refer to:
- M-Pesa Daraja API documentation: https://developer.safaricom.co.ke/
- PHPNuxBill documentation
- System logs at `pages/mpesa-webhook.html`
