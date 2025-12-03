# M-Pesa STK Push Payment Gateway - Testing Guide

## Overview
This guide provides step-by-step instructions for testing the M-Pesa payment gateway integration in PHPNuxBill.

## Prerequisites
- PHPNuxBill installation with M-Pesa gateway files
- Admin access to the system
- Customer account for testing
- M-Pesa credentials (Sandbox or Production)

## Verification Status
✓ M-Pesa gateway file exists and all required functions are defined
✓ M-Pesa is discoverable by the payment gateway system
✓ M-Pesa template file exists
✓ Helper functions are working correctly

## Test 1: Verify M-Pesa Appears in Payment Gateway List

### Steps:
1. Login as an administrator
2. Navigate to `/paymentgateway` (Settings → Payment Gateway)
3. Look for M-Pesa in the list of available payment gateways

### Expected Result:
- M-Pesa should appear in the payment gateway list alongside other gateways (PayPal, Paystack)
- There should be a checkbox next to M-Pesa
- There should be a button/link to configure M-Pesa
- There should be an "Audit" button to view M-Pesa transactions
- There should be a delete button (trash icon)

### Screenshot Checklist:
- [ ] M-Pesa appears in the list
- [ ] Checkbox is visible
- [ ] Configuration link works
- [ ] Audit link works

---

## Test 2: Enable M-Pesa Gateway

### Steps:
1. On the `/paymentgateway` page
2. Check the checkbox next to M-Pesa
3. Click "Save Changes" button at the bottom

### Expected Result:
- Success message: "Payment Gateway saved successfully"
- M-Pesa checkbox remains checked after page reload
- M-Pesa button changes from gray/default to blue/info color

### Verification:
```sql
SELECT * FROM tbl_appconfig WHERE setting = 'payment_gateway';
```
The value should include 'mpesa' in the comma-separated list.

---

## Test 3: Configure M-Pesa Credentials

### Steps:
1. Click on the M-Pesa button/link from the payment gateway list
2. You should be redirected to `/paymentgateway/mpesa`
3. Fill in the configuration form:
   - **Consumer Key**: Your Daraja API consumer key
   - **Consumer Secret**: Your Daraja API consumer secret
   - **Business Shortcode**: Your M-Pesa business shortcode
   - **Passkey**: Your Lipa Na M-Pesa passkey
   - **Environment**: Select "Sandbox" for testing or "Production" for live
4. Note the callback URL displayed (you'll need to register this with Safaricom)
5. Click "Save Changes"

### Expected Result:
- Form displays all required fields
- Callback URL is displayed and read-only
- Success message after saving: "Settings Saved Successfully"
- Configuration is persisted in database

### Sandbox Test Credentials:
For testing, you can use Safaricom's sandbox credentials:
- Get credentials from: https://developer.safaricom.co.ke/
- Create an app in the Daraja portal
- Use the sandbox credentials provided

### Verification:
```sql
SELECT * FROM tbl_appconfig WHERE setting LIKE 'mpesa_%';
```
All M-Pesa settings should be present with values.

---

## Test 4: Verify M-Pesa Appears as Payment Option

### Steps:
1. Logout from admin account
2. Login as a customer
3. Navigate to `/order/package` (Order → Buy Package)
4. Select any package
5. Click "Order" or "Buy Now"
6. You should be redirected to `/order/gateway/{router_id}/{plan_id}`

### Expected Result:
- Payment gateway selection page displays
- M-Pesa appears in the "Payment Gateway" dropdown
- Other enabled gateways also appear in the dropdown
- Package details are displayed correctly

### Screenshot Checklist:
- [ ] M-Pesa appears in dropdown
- [ ] Package name is correct
- [ ] Package price is correct
- [ ] Total amount is calculated correctly

---

## Test 5: Test Complete Payment Flow (Sandbox)

### Steps:
1. From the payment gateway selection page
2. Select "M-Pesa" from the dropdown
3. Click "Pay Now"
4. Confirm the payment when prompted

### Expected Result:
- Transaction is created in `tbl_payment_gateway` table
- You are redirected to `/order/view/{transaction_id}`
- Transaction status shows as "Pending" (status = 1)
- In sandbox mode, you should receive an STK Push prompt on your test phone
- Transaction details display:
  - CheckoutRequestID
  - Transaction amount
  - Package name
  - Status
  - "Check Status" button

### Verification:
```sql
SELECT * FROM tbl_payment_gateway WHERE gateway = 'mpesa' ORDER BY id DESC LIMIT 1;
```
Check:
- `gateway` = 'mpesa'
- `status` = 1 (pending)
- `gateway_trx_id` contains CheckoutRequestID
- `pg_request` contains STK Push request JSON
- `created_date` is set
- `expired_date` is set (2 minutes from creation)

---

## Test 6: Test STK Push Initiation

### Expected Behavior:
When you click "Pay Now" in Test 5:

1. **Phone Number Formatting**:
   - System formats your phone number to E.164 format (254XXXXXXXXX)
   - Accepts: 0712345678, 712345678, 254712345678, +254712345678

2. **OAuth Token**:
   - System obtains access token from Daraja API
   - Uses Consumer Key and Consumer Secret

3. **STK Push Request**:
   - System sends STK Push request to M-Pesa
   - Request includes:
     - Business Shortcode
     - Transaction password (Base64 encoded)
     - Amount
     - Phone number
     - Callback URL
     - Account reference (username)
     - Transaction description (plan name)

4. **Response Handling**:
   - CheckoutRequestID is stored in database
   - MerchantRequestID is stored
   - User is redirected to transaction view page

### Sandbox Testing:
- Use Safaricom test phone numbers
- Test PIN: Usually 1234 or as provided by Safaricom
- You should receive a prompt on your phone to enter PIN

---

## Test 7: Test Payment Callback (Success)

### Simulation:
In sandbox mode, complete the payment on your test phone by entering the PIN.

### Expected Result:
1. **Callback Received**:
   - M-Pesa sends callback to `/callback/mpesa`
   - Callback data is logged to `pages/mpesa-webhook.html`

2. **Transaction Updated**:
   ```sql
   SELECT * FROM tbl_payment_gateway WHERE gateway_trx_id = 'YOUR_CHECKOUT_REQUEST_ID';
   ```
   - `status` = 2 (paid)
   - `pg_paid_response` contains callback JSON
   - `payment_method` = 'M-PESA'
   - `payment_channel` = 'mpesa_stk'
   - `paid_date` is set

3. **Package Activated**:
   ```sql
   SELECT * FROM tbl_user_recharges WHERE username = 'YOUR_USERNAME' ORDER BY id DESC LIMIT 1;
   ```
   - New recharge record exists
   - `status` = 'on'
   - Package is active

4. **Telegram Notification**:
   - Admin receives Telegram notification with payment details
   - Includes: Username, Amount, M-Pesa Receipt Number

### Verification Checklist:
- [ ] Transaction status changed to "Paid"
- [ ] Package is activated
- [ ] Customer can access service
- [ ] Telegram notification received
- [ ] Callback logged in webhook file

---

## Test 8: Test Payment Callback (Failed/Cancelled)

### Simulation:
1. Initiate a payment
2. Cancel the STK Push prompt on your phone (or let it timeout)

### Expected Result:
1. **Callback Received**:
   - M-Pesa sends failure callback
   - ResultCode is not 0

2. **Transaction Updated**:
   - `status` = 4 (failed)
   - `pg_paid_response` contains failure callback
   - Package is NOT activated

3. **Telegram Notification**:
   - Admin receives failure notification
   - Includes failure reason

### Verification:
```sql
SELECT * FROM tbl_payment_gateway WHERE gateway_trx_id = 'YOUR_CHECKOUT_REQUEST_ID';
```
- `status` = 4
- `pg_paid_response` contains error details

---

## Test 9: Test Manual Status Check

### Steps:
1. Navigate to a pending transaction: `/order/view/{transaction_id}`
2. Click "Check Status" button
3. System queries M-Pesa API for transaction status

### Expected Result:
- If payment successful: Package activated, status updated to paid
- If payment pending: Message "Payment is still being processed"
- If payment failed: Status updated to failed with reason
- If payment cancelled: Status updated to failed with cancellation message

### Status Codes:
- ResultCode 0: Success
- ResultCode 1032: Cancelled by user
- ResultCode 1037: Timeout
- ResultCode 1: Insufficient balance

---

## Test 10: Test Audit Log

### Steps:
1. Login as admin
2. Navigate to `/paymentgateway/audit/mpesa`
3. View list of M-Pesa transactions

### Expected Result:
- All M-Pesa transactions are listed
- Columns display:
  - Transaction ID
  - Username
  - Gateway (mpesa)
  - CheckoutRequestID
  - Plan name
  - Amount
  - Status
  - Created date
  - Paid date
- Search functionality works
- Can click on transaction to view details

### Detail View:
Click on a transaction to view `/paymentgateway/auditview/{id}`:
- Full transaction details
- Request JSON (STK Push request)
- Response JSON (M-Pesa callback)
- All metadata

---

## Test 11: Test Error Handling

### Test Cases:

#### 11.1 Invalid Phone Number
- Enter invalid phone number format
- Expected: Error message with format examples

#### 11.2 Missing Configuration
- Disable M-Pesa configuration (delete credentials)
- Try to make payment
- Expected: Error message "Admin has not yet setup M-Pesa payment gateway"

#### 11.3 Invalid Credentials
- Enter wrong Consumer Key/Secret
- Try to make payment
- Expected: Authentication error, Telegram notification to admin

#### 11.4 Network Timeout
- Simulate network issue (if possible)
- Expected: Timeout error, option to retry

#### 11.5 Duplicate Transaction
- Try to create another transaction while one is pending
- Expected: Redirect to existing pending transaction

---

## Test 12: Test Complete User Journey

### Full Flow:
1. **Customer Registration**:
   - Register new customer account
   - Verify email address is set

2. **Browse Packages**:
   - Navigate to package list
   - View package details

3. **Select Package**:
   - Choose a package
   - Click "Order"

4. **Select Payment Method**:
   - Choose M-Pesa from dropdown
   - Review order summary
   - Click "Pay Now"

5. **Complete Payment**:
   - Receive STK Push prompt
   - Enter M-Pesa PIN
   - Wait for confirmation

6. **Verify Activation**:
   - Check transaction status
   - Verify package is active
   - Test internet access (if applicable)

7. **View History**:
   - Navigate to order history
   - View completed transaction
   - Download invoice (if available)

---

## Production Testing Checklist

Before going live with production credentials:

- [ ] All sandbox tests passed
- [ ] Callback URL registered with Safaricom
- [ ] Production credentials configured
- [ ] Environment set to "Production"
- [ ] Mikrotik walled garden configured (if using hotspot)
- [ ] Telegram bot configured for notifications
- [ ] SSL certificate valid for callback URL
- [ ] Test with small amount first
- [ ] Monitor webhook logs
- [ ] Verify reconciliation with M-Pesa statements

---

## Troubleshooting

### Issue: M-Pesa not appearing in gateway list
**Solution**: 
- Verify `mpesa.php` exists in `system/paymentgateway/`
- Check file permissions
- Clear cache

### Issue: STK Push not received
**Solution**:
- Verify phone number format
- Check if phone is M-Pesa registered
- Verify credentials are correct
- Check environment setting (sandbox vs production)

### Issue: Callback not received
**Solution**:
- Verify callback URL is registered with Safaricom
- Check firewall/walled garden settings
- Verify SSL certificate is valid
- Check webhook log file: `pages/mpesa-webhook.html`

### Issue: Package not activated after payment
**Solution**:
- Check callback logs
- Verify `Package::rechargeUser()` is called
- Check for errors in system logs
- Verify transaction status in database

---

## Monitoring and Maintenance

### Regular Checks:
1. **Daily**:
   - Monitor webhook logs
   - Check failed transactions
   - Review Telegram notifications

2. **Weekly**:
   - Reconcile with M-Pesa statements
   - Review audit logs
   - Check success rate

3. **Monthly**:
   - Review error patterns
   - Update credentials if needed
   - Test payment flow

### Log Files:
- Webhook logs: `pages/mpesa-webhook.html`
- System logs: Check PHPNuxBill logs
- Database: `tbl_payment_gateway` table

---

## Support and Resources

### Safaricom Daraja API:
- Portal: https://developer.safaricom.co.ke/
- Documentation: https://developer.safaricom.co.ke/docs
- Support: support@safaricom.co.ke

### PHPNuxBill:
- GitHub: https://github.com/hotspotbilling/phpnuxbill/
- Telegram: https://t.me/ibnux

---

## Test Results Template

Use this template to document your test results:

```
M-Pesa Integration Test Results
Date: _______________
Tester: _______________
Environment: [ ] Sandbox [ ] Production

Test 1: Gateway List Display          [ ] Pass [ ] Fail
Test 2: Enable Gateway                 [ ] Pass [ ] Fail
Test 3: Configure Credentials          [ ] Pass [ ] Fail
Test 4: Payment Option Display         [ ] Pass [ ] Fail
Test 5: Complete Payment Flow          [ ] Pass [ ] Fail
Test 6: STK Push Initiation           [ ] Pass [ ] Fail
Test 7: Success Callback              [ ] Pass [ ] Fail
Test 8: Failed Callback               [ ] Pass [ ] Fail
Test 9: Manual Status Check           [ ] Pass [ ] Fail
Test 10: Audit Log                    [ ] Pass [ ] Fail
Test 11: Error Handling               [ ] Pass [ ] Fail
Test 12: Complete User Journey        [ ] Pass [ ] Fail

Notes:
_________________________________________________
_________________________________________________
_________________________________________________

Overall Status: [ ] All Tests Passed [ ] Some Tests Failed

Approved by: _______________
Date: _______________
```

---

## Conclusion

This testing guide covers all aspects of the M-Pesa payment gateway integration. Follow each test systematically and document your results. If any test fails, refer to the troubleshooting section or contact support.

**Remember**: Always test thoroughly in sandbox mode before switching to production!
