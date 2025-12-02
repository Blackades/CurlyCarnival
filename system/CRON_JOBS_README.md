# VPN Cron Jobs - Quick Reference

## Overview
Three automated cron jobs monitor and maintain the VPN infrastructure for remote MikroTik routers.

## Cron Jobs

### 1. VPN Health Check (`cron_vpn_health.php`)
**Purpose:** Monitor VPN connection status for all remote routers

**Schedule:** Every 5 minutes
```bash
*/5 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_health.php >> /var/log/phpnuxbill/vpn-health.log 2>&1
```

**What it does:**
- Checks VPN connectivity for all remote routers
- Tests VPN IP reachability (ping)
- Tests API port accessibility
- Updates router status in database
- Logs connection events
- Sends email alerts after 3 consecutive failures

**Log file:** `/var/log/phpnuxbill/vpn-health.log`

---

### 2. Certificate Expiry Check (`cron_vpn_cert_check.php`)
**Purpose:** Monitor certificate expiration and send alerts

**Schedule:** Daily at 2 AM
```bash
0 2 * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_cert_check.php >> /var/log/phpnuxbill/vpn-cert-check.log 2>&1
```

**What it does:**
- Checks all VPN certificates for expiration
- Sends email alerts at configured intervals (30, 14, 7 days)
- Marks expired certificates as 'expired' in database
- Provides detailed expiry information

**Log file:** `/var/log/phpnuxbill/vpn-cert-check.log`

---

### 3. VPN Statistics (`cron_vpn_stats.php`)
**Purpose:** Calculate and log connection statistics

**Schedule:** Hourly
```bash
0 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_stats.php >> /var/log/phpnuxbill/vpn-stats.log 2>&1
```

**What it does:**
- Calculates 7-day and 30-day uptime percentages
- Calculates 24-hour data transfer statistics
- Generates system-wide statistics
- Logs detailed per-router metrics

**Log file:** `/var/log/phpnuxbill/vpn-stats.log`

---

## Installation

### 1. Create Log Directory
```bash
sudo mkdir -p /var/log/phpnuxbill
sudo chown www-data:www-data /var/log/phpnuxbill
sudo chmod 755 /var/log/phpnuxbill
```

### 2. Create Cron Configuration
Create file `/etc/cron.d/phpnuxbill-vpn`:
```bash
# VPN Health Check - Every 5 minutes
*/5 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_health.php >> /var/log/phpnuxbill/vpn-health.log 2>&1

# Certificate Expiry Check - Daily at 2 AM
0 2 * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_cert_check.php >> /var/log/phpnuxbill/vpn-cert-check.log 2>&1

# VPN Connection Statistics - Hourly
0 * * * * www-data /usr/bin/php /var/www/html/system/cron_vpn_stats.php >> /var/log/phpnuxbill/vpn-stats.log 2>&1
```

### 3. Set Permissions
```bash
sudo chmod 644 /etc/cron.d/phpnuxbill-vpn
sudo chown root:root /etc/cron.d/phpnuxbill-vpn
```

### 4. Reload Cron
```bash
sudo systemctl reload cron
# or on some systems:
sudo service cron reload
```

---

## Manual Testing

Test each cron job manually before enabling:

```bash
# Test health check
sudo -u www-data /usr/bin/php /var/www/html/system/cron_vpn_health.php

# Test certificate check
sudo -u www-data /usr/bin/php /var/www/html/system/cron_vpn_cert_check.php

# Test statistics
sudo -u www-data /usr/bin/php /var/www/html/system/cron_vpn_stats.php
```

---

## Log Rotation

Create `/etc/logrotate.d/phpnuxbill-vpn`:
```
/var/log/phpnuxbill/vpn-*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload rsyslog > /dev/null 2>&1 || true
    endscript
}
```

---

## Monitoring

### Check Cron Status
```bash
# View cron logs
sudo tail -f /var/log/syslog | grep CRON

# Check if cron jobs are running
sudo grep phpnuxbill /var/log/syslog
```

### Check VPN Logs
```bash
# Health check log
sudo tail -f /var/log/phpnuxbill/vpn-health.log

# Certificate check log
sudo tail -f /var/log/phpnuxbill/vpn-cert-check.log

# Statistics log
sudo tail -f /var/log/phpnuxbill/vpn-stats.log
```

### Check Last Execution
```bash
# View last 10 health checks
sudo tail -n 50 /var/log/phpnuxbill/vpn-health.log | grep "Completed Successfully"

# View last certificate check
sudo tail -n 50 /var/log/phpnuxbill/vpn-cert-check.log | grep "Certificate Check Summary"

# View last statistics run
sudo tail -n 50 /var/log/phpnuxbill/vpn-stats.log | grep "Statistics Summary"
```

---

## Troubleshooting

### Cron Jobs Not Running
1. Check cron service status:
   ```bash
   sudo systemctl status cron
   ```

2. Verify cron file syntax:
   ```bash
   sudo cat /etc/cron.d/phpnuxbill-vpn
   ```

3. Check file permissions:
   ```bash
   ls -la /etc/cron.d/phpnuxbill-vpn
   ```

### Script Errors
1. Check PHP syntax:
   ```bash
   php -l /var/www/html/system/cron_vpn_health.php
   ```

2. Run manually to see errors:
   ```bash
   sudo -u www-data /usr/bin/php /var/www/html/system/cron_vpn_health.php
   ```

3. Check log files for error messages:
   ```bash
   sudo tail -n 100 /var/log/phpnuxbill/vpn-health.log
   ```

### Database Connection Issues
1. Verify database credentials in config.php
2. Check database server is running
3. Test database connection manually

### Email Alerts Not Sending
1. Check email configuration in config.php
2. Verify SMTP settings
3. Check mail logs: `sudo tail -f /var/log/mail.log`

---

## Configuration

Alert settings are configured in `config.php`:

```php
// VPN Monitoring
$config['vpn_health_check_interval'] = 300;  // 5 minutes
$config['vpn_connection_timeout'] = 10;      // seconds
$config['vpn_max_retry_attempts'] = 3;

// Alerts
$config['vpn_alert_email'] = 'admin@example.com';
$config['vpn_alert_on_disconnect'] = true;
$config['vpn_alert_cert_expiry_days'] = [30, 14, 7];
```

---

## Performance

Expected execution times:
- **Health Check**: 1-5 seconds (depends on number of routers and network latency)
- **Certificate Check**: < 1 second (database queries only)
- **Statistics**: 1-3 seconds (depends on number of routers and log history)

---

## Dependencies

Required PHP classes:
- `VPNMonitoringService`
- `VPNConnectionLog`
- `VPNMetrics`
- `VPNAlertManager`
- `ORM` (Idiorm)

Required database tables:
- `tbl_routers`
- `tbl_vpn_connection_logs`
- `tbl_vpn_certificates`

---

## Security

- Scripts run as `www-data` user (web server user)
- No sensitive data in log files (passwords are never logged)
- Log files are readable only by www-data and root
- Cron configuration is owned by root

---

## Support

For issues or questions:
1. Check log files for error messages
2. Review phpnuxbill documentation
3. Contact system administrator
