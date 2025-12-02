# VPN Feature Database Migration

This directory contains database migration scripts for the MikroTik OpenVPN automation feature in phpnuxbill.

## Overview

The VPN feature adds the following database schema changes:

### Modified Tables

#### `tbl_routers`
New columns added:
- `connection_type` - ENUM('local', 'remote') - Identifies router connection type
- `vpn_username` - VARCHAR(50) - OpenVPN username for remote routers
- `vpn_password_hash` - VARCHAR(255) - Hashed VPN password
- `vpn_ip` - VARCHAR(45) - Assigned VPN IP address
- `certificate_path` - VARCHAR(255) - Path to certificate directory
- `certificate_expiry` - DATE - Certificate expiration date
- `ovpn_status` - ENUM('connected', 'disconnected', 'error', 'pending') - VPN connection status
- `last_vpn_check` - DATETIME - Last VPN health check timestamp
- `config_package_path` - VARCHAR(255) - Path to downloadable configuration package

### New Tables

#### `tbl_vpn_audit_log`
Tracks all VPN-related administrative actions:
- `id` - Primary key
- `router_id` - Foreign key to tbl_routers
- `action` - Action performed (create, update, delete, cert_renew, etc.)
- `admin_id` - Foreign key to tbl_users
- `details` - JSON encoded action details
- `ip_address` - Admin IP address
- `status` - Action status (success, failed, pending)
- `error_message` - Error details if failed
- `created_at` - Timestamp

#### `tbl_vpn_certificates`
Stores VPN certificate information:
- `id` - Primary key
- `router_id` - Foreign key to tbl_routers
- `client_name` - Unique certificate client name
- `certificate_path` - Path to certificate file
- `key_path` - Path to private key file
- `ca_path` - Path to CA certificate
- `ovpn_file_path` - Path to OVPN configuration file
- `issued_date` - Certificate issue date
- `expiry_date` - Certificate expiration date
- `status` - Certificate status (active, expired, revoked)
- `created_at` - Timestamp

#### `tbl_vpn_connection_logs`
Logs VPN connection events:
- `id` - Primary key
- `router_id` - Foreign key to tbl_routers
- `vpn_ip` - VPN IP address
- `connection_status` - Connection status (connected, disconnected, error)
- `bytes_sent` - Data sent in bytes
- `bytes_received` - Data received in bytes
- `connection_time` - Connection start time
- `disconnection_time` - Connection end time
- `error_details` - Error information
- `created_at` - Timestamp

## Migration Scripts

### 1. Automatic Migration (Recommended)

The migration is automatically applied during system updates via `system/updates.json`:

```bash
# Update phpnuxbill (migrations run automatically)
php update.php
```

### 2. Manual Migration

Run the standalone migration script:

```bash
# From phpnuxbill root directory
php system/migrate_vpn.php
```

**Output:**
- ✓ Success - Migration applied successfully
- ⊘ Skipped - Already exists (safe to ignore)
- ✗ Failed - Error occurred (check error message)

### 3. Full Installation

For complete VPN feature setup including directories, permissions, and cron jobs:

```bash
# Must run as root
sudo bash system/install_vpn.sh
```

This script performs:
1. Creates necessary directories
2. Sets proper permissions
3. Runs database migration
4. Configures sudo permissions
5. Sets up cron jobs
6. Configures log rotation

## Rollback

To remove the VPN feature and all related data:

```bash
# WARNING: This permanently deletes all VPN data!
php system/rollback_vpn.php
```

When prompted, type `yes` to confirm.

## Verification

After migration, verify the schema:

```sql
-- Check tbl_routers columns
DESCRIBE tbl_routers;

-- Check new tables exist
SHOW TABLES LIKE 'tbl_vpn%';

-- Verify indexes
SHOW INDEX FROM tbl_vpn_audit_log;
SHOW INDEX FROM tbl_vpn_certificates;
SHOW INDEX FROM tbl_vpn_connection_logs;
```

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.2 or higher
- PDO MySQL extension
- Admin/SuperAdmin access

## Troubleshooting

### Migration Fails with "Column already exists"

This is normal if the migration was previously run. The script will skip existing columns.

### Foreign Key Constraint Errors

Ensure `tbl_routers` and `tbl_users` tables exist before running migration.

### Permission Denied

Ensure the database user has ALTER and CREATE privileges:

```sql
GRANT ALTER, CREATE, INDEX ON phpnuxbill.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
```

### Rollback Fails

If rollback fails, manually drop tables and columns:

```sql
-- Drop tables
DROP TABLE IF EXISTS tbl_vpn_connection_logs;
DROP TABLE IF EXISTS tbl_vpn_certificates;
DROP TABLE IF EXISTS tbl_vpn_audit_log;

-- Remove columns from tbl_routers
ALTER TABLE tbl_routers 
  DROP COLUMN IF EXISTS config_package_path,
  DROP COLUMN IF EXISTS last_vpn_check,
  DROP COLUMN IF EXISTS ovpn_status,
  DROP COLUMN IF EXISTS certificate_expiry,
  DROP COLUMN IF EXISTS certificate_path,
  DROP COLUMN IF EXISTS vpn_ip,
  DROP COLUMN IF EXISTS vpn_password_hash,
  DROP COLUMN IF EXISTS vpn_username,
  DROP COLUMN IF EXISTS connection_type;
```

## Migration Version

- **Version:** 2025.12.3.vpn
- **Date:** December 3, 2025
- **Requirements:** 1.4, 11.4

## Support

For issues or questions:
1. Check the main documentation
2. Review error logs in `/var/log/phpnuxbill/`
3. Verify database user permissions
4. Check phpnuxbill system requirements

## Files

- `migrate_vpn.php` - Standalone migration script
- `rollback_vpn.php` - Rollback script
- `install_vpn.sh` - Full installation script (Linux)
- `updates.json` - Automatic migration definitions
- `VPN_MIGRATION_README.md` - This file

## Notes

- All migrations are idempotent (safe to run multiple times)
- Existing data in `tbl_routers` is preserved
- Foreign key constraints ensure data integrity
- Indexes optimize query performance for monitoring
- Timestamps use server timezone

## Next Steps

After successful migration:

1. Configure VPN settings in `config.php`
2. Set up OpenVPN server
3. Configure EasyRSA for certificates
4. Create VPN management bash scripts
5. Test by adding a remote router

---

**Last Updated:** December 3, 2025
