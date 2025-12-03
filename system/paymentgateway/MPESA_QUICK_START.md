# M-Pesa Quick Start Guide

## ✅ Integration Status: COMPLETE & READY

M-Pesa STK Push is fully integrated and ready to use!

## Quick Verification (30 seconds)

Run this command to verify everything is working:
```bash
php phpnuxbill-fresh/system/verify_mpesa_simple.php
```

Expected output: All checks should show ✓ (green checkmarks)

## Quick Setup (5 minutes)

### Step 1: Enable M-Pesa
1. Login as admin
2. Go to: **Settings → Payment Gateway** (`/paymentgateway`)
3. Check the **M-Pesa** checkbox
4. Click **Save Changes**

### Step 2: Configure Credentials
1. Click on the **M-Pesa** button
2. Fill in the form:
   - **Consumer Key**: Get from Daraja portal
   - **Consumer Secret**: Get from Daraja portal
   - **Business Shortcode**: Your M-Pesa shortcode
   - **Passkey**: Your Lipa Na M-Pesa passkey
   - **Environment**: Select **Sandbox** (for testing)
3. Note the **Callback URL** displayed
4. Click **Save Changes**

### Step 3: Register Callback URL
1. Login to Safaricom Daraja portal
2. Go to your app settings
3. Register this URL: `https://yourdomain.com/callback/mpesa`

### Step 4: Test Payment
1. Logout and login as a customer
2. Go to: **Order → Buy Package** (`/order/package`)
3. Select any package
4. Choose **M-Pesa** from dropdown
5. Click **Pay Now**
6. Enter M-Pesa PIN on your phone
7. Wait for confirmation

## Where to Get Credentials

### Sandbox (Testing)
1. Visit: https://developer.safaricom.co.ke/
2. Create account and login
3. Create a new app
4. Get test credentials from app dashboard
5. Use test phone numbers provided

### Production (Live)
1. Contact Safaricom business support
2. Apply for Lipa Na M-Pesa Online
3. Get production credentials
4. Register production callback URL

## URLs You Need to Know

| Purpose | URL |
|---------|-----|
| Gateway List | `/paymentgateway` |
| M-Pesa Config | `/paymentgateway/mpesa` |
| M-Pesa Audit | `/paymentgateway/audit/mpesa` |
| Buy Package | `/order/package` |
| Callback | `/callback/mpesa` |

## Phone Number Formats

All these formats work:
- `0712345678` ✓
- `712345678` ✓
- `254712345678` ✓
- `+254712345678` ✓

System automatically converts to: `254712345678`

## Troubleshooting

### M-Pesa not in list?
- Check file exists: `system/paymentgateway/mpesa.php`
- Clear browser cache
- Refresh page

### STK Push not received?
- Verify phone number is M-Pesa registered
- Check credentials are correct
- Verify environment setting (Sandbox vs Production)

### Callback not working?
- Check callback URL is registered with Safaricom
- Verify SSL certificate is valid
- Check webhook log: `pages/mpesa-webhook.html`

### Package not activated?
- Check transaction status in database
- View audit log: `/paymentgateway/audit/mpesa`
- Check system logs for errors

## Quick Database Checks

### Check if M-Pesa is enabled
```sql
SELECT * FROM tbl_appconfig WHERE setting = 'payment_gateway';
```
Should include 'mpesa' in the value.

### Check M-Pesa configuration
```sql
SELECT * FROM tbl_appconfig WHERE setting LIKE 'mpesa_%';
```
Should show 5 settings with values.

### View recent transactions
```sql
SELECT * FROM tbl_payment_gateway 
WHERE gateway = 'mpesa' 
ORDER BY id DESC 
LIMIT 10;
```

## Status Codes

| Code | Meaning | Action |
|------|---------|--------|
| 0 | Success | Package activated |
| 1 | Insufficient balance | Customer needs more funds |
| 1032 | Cancelled by user | Transaction failed |
| 1037 | Timeout | Customer didn't respond |

## Transaction Statuses

| Status | Meaning |
|--------|---------|
| 1 | Pending (waiting for payment) |
| 2 | Paid (package activated) |
| 3 | Expired (timeout) |
| 4 | Failed (cancelled or error) |

## Support & Documentation

### Full Documentation
- **Testing Guide**: `MPESA_TESTING_GUIDE.md` (12 detailed tests)
- **Integration Summary**: `MPESA_INTEGRATION_SUMMARY.md` (complete overview)
- **Completion Report**: `TASK_9_COMPLETION_REPORT.md` (verification results)

### External Resources
- **Daraja Portal**: https://developer.safaricom.co.ke/
- **Daraja Docs**: https://developer.safaricom.co.ke/docs
- **PHPNuxBill**: https://github.com/hotspotbilling/phpnuxbill/

### Get Help
- **Safaricom Support**: support@safaricom.co.ke
- **PHPNuxBill Telegram**: https://t.me/ibnux

## Mikrotik Walled Garden

If using hotspot, add these:
```
/ip hotspot walled-garden
add dst-host=safaricom.co.ke
add dst-host=*.safaricom.co.ke
```

## Production Checklist

Before going live:
- [ ] All sandbox tests passed
- [ ] Production credentials obtained
- [ ] Callback URL registered with Safaricom
- [ ] Environment set to "Production"
- [ ] SSL certificate valid
- [ ] Mikrotik walled garden configured
- [ ] Telegram bot configured
- [ ] Test with small amount
- [ ] Monitor webhook logs

## Quick Test Sequence

1. **Admin Test** (2 minutes):
   - [ ] M-Pesa appears in gateway list
   - [ ] Can enable M-Pesa
   - [ ] Configuration page loads
   - [ ] Can save credentials

2. **Customer Test** (3 minutes):
   - [ ] M-Pesa appears in payment dropdown
   - [ ] Can initiate payment
   - [ ] Receive STK Push prompt
   - [ ] Can complete payment

3. **Verification** (1 minute):
   - [ ] Transaction status updated
   - [ ] Package activated
   - [ ] Audit log shows transaction

## That's It!

M-Pesa is ready to use. Start with sandbox testing, then move to production.

**Need more details?** Check the full documentation files listed above.

**Questions?** Contact Safaricom support or PHPNuxBill community.

---

**Quick Start Guide** | M-Pesa STK Push Integration | PHPNuxBill
