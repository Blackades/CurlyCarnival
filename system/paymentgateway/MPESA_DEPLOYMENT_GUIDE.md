# M-Pesa STK Push - Production Deployment Guide

## Table of Contents
1. [Production Credential Setup](#production-credential-setup)
2. [Callback URL Registration](#callback-url-registration)
3. [Mikrotik Walled Garden Configuration](#mikrotik-walled-garden-configuration)
4. [Troubleshooting Common Issues](#troubleshooting-common-issues)
5. [Post-Deployment Checklist](#post-deployment-checklist)
6. [Monitoring and Maintenance](#monitoring-and-maintenance)

---

## Production Credential Setup

### Prerequisites
- Active M-Pesa business account (Paybill or Till Number)
- Registered business with Safaricom
- Access to Safaricom Daraja Portal

### Step 1: Register on Daraja Portal

1. **Visit Daraja Portal**
   - Go to: https://developer.safaricom.co.ke/
   - Click "Sign Up" if you don't have an account
   - Complete registration with your business email

2. **Verify Your Account**
   - Check your email for verification link
   - Click the link to activate your account
   - Log in to the Daraja Portal

### Step 2: Create Production App

1. **Navigate to My Apps**
   - Click on "My Apps" in the dashboard
   - Click "Add a New App" button

2. **Fill App Details**
   - **App Name**: PHPNuxBill M-Pesa Integration (or your preferred name)
   - **Description**: M-Pesa payment gateway for PHPNuxBill billing system
   - **Select APIs**: Check "Lipa Na M-Pesa Online"
   - Click "Create App"

3. **Note Your Credentials**
   - After creation, you'll see:
     - **Consumer Key**: Copy and save securely
     - **Consumer Secret**: Copy and save securely
   - These are your production credentials

### Step 3: Obtain Business Shortcode and Passkey

1. **Contact Safaricom Business Support**
   - Email: apisupport@safaricom.co.ke
   - Phone: +254 711 051 000
   - Provide your:
     - Business name
     - Paybill/Till number
     - Daraja Portal email

2. **Request Lipa Na M-Pesa Online Activation**
   - Request activation of STK Push for your shortcode
   - Ask for your **Lipa Na M-Pesa Passkey**
   - This process typically takes 1-3 business days

3. **Receive Credentials**
   - You'll receive via email:
     - **Business Shortcode**: Your Paybill/Till number
     - **Passkey**: Long alphanumeric string (keep secure)

### Step 4: Configure PHPNuxBill

1. **Log in as Admin**
   - Access your PHPNuxBill admin panel
   - Navigate to: **Settings → Payment Gateway**

2. **Select M-Pesa**
   - Click on "M-Pesa" from the gateway list
   - Click "Configure" or the settings icon

3. **Enter Production Credentials**
   ```
   Consumer Key:     [Paste from Daraja Portal]
   Consumer Secret:  [Paste from Daraja Portal]
   Business Shortcode: [Your Paybill/Till number]
   Passkey:          [Received from Safaricom]
   Environment:      Production
   ```

4. **Save Configuration**
   - Click "Save" button
   - Verify success message appears

5. **Copy Callback URL**
   - Note the callback URL displayed (e.g., `https://yourdomain.com/callback/mpesa`)
   - You'll need this for the next section

---

## Callback URL Registration

### Why Register Callback URL?

M-Pesa needs to know where to send payment notifications. Without registering your callback URL, you won't receive payment confirmations, and packages won't be activated automatically.

### Method 1: Via Daraja Portal (Recommended)

1. **Log in to Daraja Portal**
   - Go to: https://developer.safaricom.co.ke/
   - Log in with your credentials

2. **Navigate to Your App**
   - Click "My Apps"
   - Select your PHPNuxBill app

3. **Configure Callback URLs**
   - Look for "Lipa Na M-Pesa Online" section
   - Find "Callback URL" or "Validation URL" field
   - Enter your callback URL: `https://yourdomain.com/callback/mpesa`
   - **Important**: Must be HTTPS (SSL certificate required)
   - Click "Save" or "Update"

### Method 2: Contact Safaricom Support

If you cannot configure via portal:

1. **Email Safaricom API Support**
   - To: apisupport@safaricom.co.ke
   - Subject: Callback URL Registration for [Your Business Name]
   
2. **Email Template**
   ```
   Dear Safaricom API Support,

   I would like to register my callback URL for Lipa Na M-Pesa Online.

   Business Details:
   - Business Name: [Your Business Name]
   - Shortcode: [Your Paybill/Till Number]
   - Daraja App Name: [Your App Name]
   
   Callback URL: https://yourdomain.com/callback/mpesa
   
   Please confirm once the registration is complete.

   Thank you,
   [Your Name]
   [Contact Phone]
   ```

3. **Wait for Confirmation**
   - Safaricom will confirm registration (usually within 24-48 hours)
   - Test your integration after confirmation

### Callback URL Requirements

✅ **Must Have:**
- HTTPS protocol (SSL certificate installed)
- Publicly accessible (not localhost or internal IP)
- Responds with HTTP 200 OK
- Can handle POST requests
- No authentication required (M-Pesa doesn't support auth headers)

❌ **Avoid:**
- HTTP (non-secure) URLs
- URLs with authentication
- URLs behind VPN or firewall
- Localhost or 127.0.0.1
- Dynamic IPs without domain names

### Testing Callback URL

1. **Test Accessibility**
   ```bash
   curl -X POST https://yourdomain.com/callback/mpesa \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```
   - Should return HTTP 200 OK

2. **Check SSL Certificate**
   ```bash
   curl -I https://yourdomain.com
   ```
   - Should not show SSL errors

3. **Verify in Logs**
   - After test transaction, check: `phpnuxbill-fresh/pages/mpesa-webhook.html`
   - Should contain callback data

---

## Mikrotik Walled Garden Configuration

### Why Configure Walled Garden?

If you're using Mikrotik hotspot, customers need to access M-Pesa servers to complete payments BEFORE they have internet access. Walled garden allows specific domains to be accessed without authentication.

### Step 1: Access Mikrotik Router

1. **Connect to Mikrotik**
   - Via Winbox: Download from mikrotik.com
   - Via WebFig: http://[router-ip]
   - Via SSH: `ssh admin@[router-ip]`

2. **Log in with Admin Credentials**

### Step 2: Add Walled Garden Rules

#### Via Winbox

1. **Navigate to IP → Hotspot**
2. **Click "Walled Garden" tab**
3. **Click "+" to add new rule**
4. **Add the following entries:**

   | Dst. Host | Action |
   |-----------|--------|
   | `*.safaricom.co.ke` | allow |
   | `safaricom.co.ke` | allow |
   | `*.mpesa.safaricom.co.ke` | allow |
   | `api.safaricom.co.ke` | allow |
   | `sandbox.safaricom.co.ke` | allow |

5. **Click "OK" for each entry**

#### Via Terminal/SSH

```bash
/ip hotspot walled-garden
add dst-host=*.safaricom.co.ke action=allow comment="M-Pesa API"
add dst-host=safaricom.co.ke action=allow comment="M-Pesa Main"
add dst-host=*.mpesa.safaricom.co.ke action=allow comment="M-Pesa Services"
add dst-host=api.safaricom.co.ke action=allow comment="M-Pesa Production API"
add dst-host=sandbox.safaricom.co.ke action=allow comment="M-Pesa Sandbox"
```

#### Via WebFig

1. **Navigate to IP → Hotspot → Walled Garden**
2. **Click "Add New"**
3. **For each domain:**
   - Dst. Host: `*.safaricom.co.ke`
   - Action: allow
   - Comment: M-Pesa API
4. **Click "OK"**
5. **Repeat for all domains listed above**

### Step 3: Add Your Server to Walled Garden

Allow customers to access your PHPNuxBill server:

```bash
/ip hotspot walled-garden
add dst-host=yourdomain.com action=allow comment="PHPNuxBill Server"
add dst-host=*.yourdomain.com action=allow comment="PHPNuxBill Subdomains"
```

Replace `yourdomain.com` with your actual domain.

### Step 4: Verify Configuration

1. **List Walled Garden Rules**
   ```bash
   /ip hotspot walled-garden print
   ```

2. **Test from Customer Device**
   - Connect to hotspot (without logging in)
   - Try accessing: https://api.safaricom.co.ke
   - Should be accessible
   - Try accessing: https://google.com
   - Should be blocked (redirect to login)

### Additional Firewall Rules (If Needed)

If you have strict firewall rules:

```bash
/ip firewall filter
add chain=forward action=accept dst-address-list=mpesa-servers comment="Allow M-Pesa"

/ip firewall address-list
add list=mpesa-servers address=196.201.214.0/24 comment="Safaricom M-Pesa"
add list=mpesa-servers address=196.201.213.0/24 comment="Safaricom M-Pesa"
```

---

## Troubleshooting Common Issues

### Issue 1: "Admin has not yet setup M-Pesa payment gateway"

**Symptoms:**
- Error message when customer tries to pay
- M-Pesa option not working

**Causes:**
- Missing credentials in configuration
- Empty Consumer Key, Secret, Shortcode, or Passkey

**Solutions:**
1. Log in as admin
2. Go to Settings → Payment Gateway → M-Pesa
3. Verify all fields are filled:
   - Consumer Key (not empty)
   - Consumer Secret (not empty)
   - Business Shortcode (not empty)
   - Passkey (not empty)
4. Save configuration
5. Test again

---

### Issue 2: "Invalid Access Token"

**Symptoms:**
- Payment fails immediately
- Error in logs: "401 Unauthorized"
- Telegram notification about authentication error

**Causes:**
- Incorrect Consumer Key or Secret
- Using sandbox credentials in production mode
- Using production credentials in sandbox mode

**Solutions:**
1. **Verify Credentials**
   - Log in to Daraja Portal
   - Check Consumer Key and Secret match exactly
   - No extra spaces or characters

2. **Check Environment Mode**
   - If using production credentials, set Environment to "Production"
   - If using sandbox credentials, set Environment to "Sandbox"

3. **Regenerate Credentials**
   - In Daraja Portal, regenerate Consumer Secret
   - Update in PHPNuxBill configuration
   - Save and test

---

### Issue 3: STK Push Not Received on Phone

**Symptoms:**
- Customer doesn't receive payment prompt
- Transaction shows as pending
- No error message

**Causes:**
- Invalid phone number format
- Phone not registered for M-Pesa
- Network issues
- Shortcode not activated for STK Push

**Solutions:**
1. **Verify Phone Number**
   - Must be Safaricom number (07XX XXX XXX)
   - Must be registered for M-Pesa
   - Try format: 0712345678 or 254712345678

2. **Check Shortcode Activation**
   - Contact Safaricom: apisupport@safaricom.co.ke
   - Confirm STK Push is activated for your shortcode
   - Request activation if not done

3. **Test with Different Phone**
   - Try with another Safaricom number
   - Ensure phone has M-Pesa app or SIM toolkit

4. **Check Logs**
   - View: `phpnuxbill-fresh/system/logs/`
   - Look for API response errors
   - Check for "Invalid phone number" messages

---

### Issue 4: Payment Successful but Package Not Activated

**Symptoms:**
- Customer pays successfully
- Receives M-Pesa confirmation SMS
- Package not activated in PHPNuxBill
- Transaction shows as pending

**Causes:**
- Callback URL not registered with Safaricom
- Callback URL not accessible
- Firewall blocking M-Pesa servers
- SSL certificate issues

**Solutions:**
1. **Verify Callback Registration**
   - Check Daraja Portal for registered callback URL
   - If not registered, follow [Callback URL Registration](#callback-url-registration)

2. **Test Callback URL Accessibility**
   ```bash
   curl -X POST https://yourdomain.com/callback/mpesa \
     -H "Content-Type: application/json" \
     -d '{"test": "data"}'
   ```
   - Should return HTTP 200 OK
   - If fails, check web server configuration

3. **Check SSL Certificate**
   ```bash
   curl -I https://yourdomain.com
   ```
   - Must have valid SSL certificate
   - No self-signed certificates
   - Use Let's Encrypt if needed

4. **Check Callback Logs**
   - View: `phpnuxbill-fresh/pages/mpesa-webhook.html`
   - If empty, callbacks not reaching server
   - If has data, check for processing errors

5. **Manual Status Check**
   - Customer can click "Check Status" on transaction page
   - This queries M-Pesa directly
   - Will activate package if payment confirmed

---

### Issue 5: "The service request is processed successfully" but Status Still Pending

**Symptoms:**
- M-Pesa confirms payment
- Customer receives SMS
- PHPNuxBill shows pending
- Callback received but not processed

**Causes:**
- Database error during package activation
- Insufficient balance in customer account
- Plan/package no longer exists
- Router connection issues

**Solutions:**
1. **Check Callback Logs**
   - View: `phpnuxbill-fresh/pages/mpesa-webhook.html`
   - Look for error messages
   - Check ResultCode (0 = success)

2. **Verify Database**
   - Check `tbl_payment_gateway` table
   - Look for transaction with CheckoutRequestID
   - Check `pg_paid_response` field for callback data

3. **Manual Package Activation**
   - Go to admin panel
   - Navigate to Payment Gateway → Audit
   - Find the transaction
   - Click "Activate" or manually recharge customer

4. **Check System Logs**
   - View: `phpnuxbill-fresh/system/logs/`
   - Look for Package::rechargeUser errors
   - Check for database connection issues

---

### Issue 6: "Invalid Shortcode"

**Symptoms:**
- Error message: "Invalid Shortcode"
- Payment fails immediately
- API returns error code

**Causes:**
- Incorrect Business Shortcode in configuration
- Shortcode not activated for Lipa Na M-Pesa Online
- Using Till Number instead of Paybill (or vice versa)

**Solutions:**
1. **Verify Shortcode**
   - Check with Safaricom what type you have (Paybill or Till)
   - Ensure shortcode matches exactly
   - No spaces or extra characters

2. **Confirm Activation**
   - Email: apisupport@safaricom.co.ke
   - Confirm your shortcode is activated for STK Push
   - Request activation if needed

3. **Check Passkey Match**
   - Passkey must match the shortcode
   - If you have multiple shortcodes, ensure correct passkey

---

### Issue 7: Callback URL Returns 404 Not Found

**Symptoms:**
- Callback logs show 404 errors
- M-Pesa cannot reach callback URL
- Payments not confirmed automatically

**Causes:**
- Incorrect URL routing
- .htaccess issues
- Web server configuration
- PHPNuxBill routing not working

**Solutions:**
1. **Verify URL Structure**
   - Correct format: `https://yourdomain.com/callback/mpesa`
   - Not: `https://yourdomain.com/callback.php?gateway=mpesa`

2. **Check .htaccess**
   - Ensure mod_rewrite is enabled
   - Verify .htaccess file exists in root
   - Check rewrite rules are correct

3. **Test Routing**
   ```bash
   curl -X POST https://yourdomain.com/callback/mpesa \
     -H "Content-Type: application/json" \
     -d '{"Body":{"stkCallback":{"ResultCode":0}}}'
   ```
   - Should return 200 OK, not 404

4. **Check Web Server Logs**
   - Apache: `/var/log/apache2/error.log`
   - Nginx: `/var/log/nginx/error.log`
   - Look for routing errors

---

### Issue 8: Timeout Errors

**Symptoms:**
- "Request timeout" message
- STK Push takes too long
- Customer doesn't receive prompt in time

**Causes:**
- Network connectivity issues
- M-Pesa service delays
- Server timeout settings too low

**Solutions:**
1. **Increase PHP Timeout**
   - Edit `php.ini`:
     ```ini
     max_execution_time = 300
     default_socket_timeout = 300
     ```
   - Restart web server

2. **Check Network**
   - Ping M-Pesa servers:
     ```bash
     ping api.safaricom.co.ke
     ```
   - Check for packet loss

3. **Retry Payment**
   - Customer can try again
   - Usually resolves on second attempt

4. **Contact Safaricom**
   - If persistent, report to apisupport@safaricom.co.ke
   - Provide timestamp and CheckoutRequestID

---

### Issue 9: "Insufficient Balance" for Customer with Funds

**Symptoms:**
- Customer has M-Pesa balance
- Payment fails with "Insufficient funds"
- Balance is more than transaction amount

**Causes:**
- M-Pesa daily transaction limits
- Account restrictions
- Pending transactions
- M-Pesa service charges not considered

**Solutions:**
1. **Check M-Pesa Limits**
   - Daily transaction limit: KES 150,000
   - Per transaction limit: KES 150,000
   - Customer may have reached limit

2. **Account Verification**
   - Customer should check M-Pesa account status
   - Dial *234# to check balance
   - Ensure account not restricted

3. **Consider Service Charges**
   - M-Pesa charges apply
   - Customer needs amount + charges
   - Display this information to customers

---

### Issue 10: Duplicate Transactions

**Symptoms:**
- Customer charged twice
- Multiple transactions for same payment
- Duplicate CheckoutRequestIDs

**Causes:**
- Customer clicked pay button multiple times
- Network issues causing retries
- Callback received multiple times

**Solutions:**
1. **Check Transaction Status**
   - View Payment Gateway → Audit
   - Look for duplicate CheckoutRequestIDs
   - Check which one is actually paid

2. **Refund Duplicate**
   - Contact Safaricom for refund
   - Provide MpesaReceiptNumber
   - Usually processed within 24 hours

3. **Prevention**
   - Code already handles duplicate callbacks
   - Disable pay button after first click
   - Show loading indicator

---

## Post-Deployment Checklist

### Initial Setup Verification

- [ ] Production credentials configured in PHPNuxBill
- [ ] Environment set to "Production"
- [ ] Callback URL registered with Safaricom
- [ ] SSL certificate installed and valid
- [ ] Mikrotik walled garden configured (if applicable)
- [ ] Telegram bot configured for notifications

### Testing Checklist

- [ ] Test with small amount (KES 1)
- [ ] Verify STK Push received on phone
- [ ] Complete payment with M-Pesa PIN
- [ ] Verify callback received (check mpesa-webhook.html)
- [ ] Verify package activated automatically
- [ ] Verify transaction marked as paid
- [ ] Test manual status check
- [ ] Test with cancelled payment
- [ ] Test with insufficient balance scenario
- [ ] Verify audit logs show all details

### Security Checklist

- [ ] Credentials stored securely
- [ ] HTTPS enabled on all pages
- [ ] Callback URL accessible only via POST
- [ ] No credentials in logs or error messages
- [ ] Firewall rules configured
- [ ] Regular backup of transaction data

### Monitoring Setup

- [ ] Telegram notifications working
- [ ] Log rotation configured
- [ ] Callback logs being written
- [ ] Database backups scheduled
- [ ] Alert system for failed payments

---

## Monitoring and Maintenance

### Daily Monitoring

1. **Check Telegram Notifications**
   - Review payment success/failure notifications
   - Investigate any authentication errors
   - Monitor for unusual patterns

2. **Review Callback Logs**
   - Location: `phpnuxbill-fresh/pages/mpesa-webhook.html`
   - Check for errors or failed callbacks
   - Verify callbacks being received

3. **Check Transaction Status**
   - Go to Payment Gateway → Audit
   - Filter by M-Pesa
   - Review pending transactions
   - Follow up on stuck payments

### Weekly Maintenance

1. **Reconcile Transactions**
   - Compare PHPNuxBill records with M-Pesa statement
   - Identify any discrepancies
   - Resolve missing payments

2. **Review Error Logs**
   - Check system logs for M-Pesa errors
   - Identify recurring issues
   - Take corrective action

3. **Test Payment Flow**
   - Make test transaction
   - Verify end-to-end flow working
   - Document any issues

### Monthly Tasks

1. **Credential Rotation**
   - Consider rotating Consumer Secret
   - Update in PHPNuxBill configuration
   - Test after rotation

2. **Performance Review**
   - Check average payment completion time
   - Review success rate
   - Identify bottlenecks

3. **Update Documentation**
   - Document any new issues encountered
   - Update troubleshooting guide
   - Share knowledge with team

### Backup Strategy

1. **Database Backups**
   - Daily backup of `tbl_payment_gateway`
   - Weekly full database backup
   - Store backups securely off-site

2. **Log Backups**
   - Archive callback logs monthly
   - Keep for at least 12 months
   - Compress old logs

3. **Configuration Backups**
   - Backup M-Pesa configuration
   - Document all settings
   - Store securely

### Emergency Contacts

**Safaricom M-Pesa Support:**
- Email: apisupport@safaricom.co.ke
- Phone: +254 711 051 000
- Business Hours: Monday-Friday, 8AM-5PM EAT

**Daraja Portal:**
- URL: https://developer.safaricom.co.ke/
- Support: Via portal ticket system

**PHPNuxBill Support:**
- GitHub: https://github.com/hotspotbilling/phpnuxbill
- Community: PHPNuxBill forums

---

## Additional Resources

### Official Documentation
- Daraja API Documentation: https://developer.safaricom.co.ke/docs
- M-Pesa API Reference: https://developer.safaricom.co.ke/APIs/MpesaExpressSimulate
- PHPNuxBill Documentation: https://github.com/hotspotbilling/phpnuxbill/wiki

### Related Guides
- `MPESA_QUICK_START.md` - Quick setup guide
- `MPESA_TESTING_GUIDE.md` - Sandbox testing instructions
- `MPESA_AUDIT_GUIDE.md` - Transaction audit and reporting

### Support Channels
- PHPNuxBill GitHub Issues
- Safaricom Developer Portal
- Community Forums

---

## Revision History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | 2024-12-03 | Initial deployment guide |

---

**Document Maintained By:** PHPNuxBill Development Team  
**Last Updated:** December 3, 2024  
**Status:** Production Ready
