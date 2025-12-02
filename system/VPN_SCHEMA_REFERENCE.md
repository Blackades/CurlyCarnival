# VPN Feature Database Schema Reference

Quick reference for the VPN feature database schema.

## Table: tbl_routers (Modified)

### New Columns

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| connection_type | ENUM('local', 'remote') | 'local' | Router connection type |
| vpn_username | VARCHAR(50) | NULL | OpenVPN authentication username |
| vpn_password_hash | VARCHAR(255) | NULL | Hashed VPN password (Argon2ID) |
| vpn_ip | VARCHAR(45) | NULL | Assigned VPN IP address (e.g., 10.8.0.2) |
| certificate_path | VARCHAR(255) | NULL | Path to certificate directory |
| certificate_expiry | DATE | NULL | Certificate expiration date |
| ovpn_status | ENUM | 'pending' | VPN connection status |
| last_vpn_check | DATETIME | NULL | Last health check timestamp |
| config_package_path | VARCHAR(255) | NULL | Path to downloadable ZIP package |

### ENUM Values

**connection_type:**
- `local` - Router on local network (direct access)
- `remote` - Router requires VPN connection

**ovpn_status:**
- `connected` - VPN tunnel is active
- `disconnected` - VPN tunnel is down
- `error` - Connection error occurred
- `pending` - Initial state, not yet configured

## Table: tbl_vpn_audit_log (New)

Audit trail for all VPN-related administrative actions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique log entry ID |
| router_id | INT | FOREIGN KEY → tbl_routers(id) | Associated router |
| action | VARCHAR(50) | NOT NULL | Action performed |
| admin_id | INT | FOREIGN KEY → tbl_users(id) | Admin who performed action |
| details | TEXT | NULL | JSON encoded action details |
| ip_address | VARCHAR(45) | NULL | Admin's IP address |
| status | ENUM | DEFAULT 'pending' | Action result |
| error_message | TEXT | NULL | Error details if failed |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Action timestamp |

### Indexes
- `idx_router_id` on `router_id`
- `idx_created_at` on `created_at`

### Action Types
- `vpn_user_created` - VPN user added to OpenVPN
- `vpn_user_updated` - VPN password changed
- `vpn_user_deleted` - VPN user removed
- `certificate_generated` - New certificate created
- `certificate_renewed` - Certificate renewed
- `certificate_revoked` - Certificate revoked
- `config_downloaded` - Configuration package downloaded
- `connection_tested` - VPN connection test performed
- `router_created` - Remote router created
- `router_updated` - Remote router updated
- `router_deleted` - Remote router deleted

### Status Values
- `success` - Action completed successfully
- `failed` - Action failed with error
- `pending` - Action in progress

## Table: tbl_vpn_certificates (New)

Certificate management and tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique certificate ID |
| router_id | INT | FOREIGN KEY → tbl_routers(id) | Associated router |
| client_name | VARCHAR(100) | UNIQUE, NOT NULL | Certificate CN (Common Name) |
| certificate_path | VARCHAR(255) | NOT NULL | Path to .crt file |
| key_path | VARCHAR(255) | NOT NULL | Path to .key file |
| ca_path | VARCHAR(255) | NOT NULL | Path to ca.crt file |
| ovpn_file_path | VARCHAR(255) | NOT NULL | Path to .ovpn config file |
| issued_date | DATETIME | NOT NULL | Certificate issue date |
| expiry_date | DATETIME | NOT NULL | Certificate expiration date |
| status | ENUM | DEFAULT 'active' | Certificate status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation timestamp |

### Indexes
- `idx_expiry_date` on `expiry_date` (for expiry checks)
- `idx_status` on `status` (for filtering)

### Status Values
- `active` - Certificate is valid and in use
- `expired` - Certificate has expired
- `revoked` - Certificate has been revoked

### Certificate Naming Convention
- Client Name: `mikrotik-{router_name}-{timestamp}`
- Example: `mikrotik-branch-office-1-20251203`

## Table: tbl_vpn_connection_logs (New)

Connection history and monitoring data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique log entry ID |
| router_id | INT | FOREIGN KEY → tbl_routers(id) | Associated router |
| vpn_ip | VARCHAR(45) | NULL | VPN IP address at time of log |
| connection_status | ENUM | NOT NULL | Connection status |
| bytes_sent | BIGINT | DEFAULT 0 | Data sent (bytes) |
| bytes_received | BIGINT | DEFAULT 0 | Data received (bytes) |
| connection_time | DATETIME | NULL | Connection start time |
| disconnection_time | DATETIME | NULL | Connection end time |
| error_details | TEXT | NULL | Error information |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Log entry timestamp |

### Indexes
- `idx_router_id` on `router_id` (for router history)
- `idx_created_at` on `created_at` (for time-based queries)

### Connection Status Values
- `connected` - VPN connection established
- `disconnected` - VPN connection lost
- `error` - Connection error occurred

### Usage Examples

**Calculate uptime:**
```sql
SELECT 
    router_id,
    SUM(TIMESTAMPDIFF(SECOND, connection_time, COALESCE(disconnection_time, NOW()))) as total_seconds
FROM tbl_vpn_connection_logs
WHERE connection_status = 'connected'
GROUP BY router_id;
```

**Get recent disconnections:**
```sql
SELECT * FROM tbl_vpn_connection_logs
WHERE connection_status = 'disconnected'
ORDER BY created_at DESC
LIMIT 10;
```

## Relationships

```
tbl_routers (1) ←→ (N) tbl_vpn_audit_log
tbl_routers (1) ←→ (N) tbl_vpn_certificates
tbl_routers (1) ←→ (N) tbl_vpn_connection_logs
tbl_users (1) ←→ (N) tbl_vpn_audit_log
```

## Foreign Key Constraints

All foreign keys use `ON DELETE CASCADE`:
- When a router is deleted, all related VPN records are automatically deleted
- When an admin user is deleted, their audit log entries remain but admin_id becomes invalid

## Storage Requirements

Estimated storage per remote router:
- tbl_routers: ~1 KB additional per router
- tbl_vpn_audit_log: ~500 bytes per action
- tbl_vpn_certificates: ~500 bytes per certificate
- tbl_vpn_connection_logs: ~200 bytes per log entry

For 100 remote routers with 1 year of logs:
- Audit logs: ~50 MB (assuming 100 actions per router)
- Connection logs: ~100 MB (assuming hourly checks)
- Total: ~150 MB additional database storage

## Maintenance Queries

### Clean old connection logs (older than 90 days)
```sql
DELETE FROM tbl_vpn_connection_logs
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Find certificates expiring soon
```sql
SELECT r.name, c.expiry_date, DATEDIFF(c.expiry_date, NOW()) as days_left
FROM tbl_vpn_certificates c
JOIN tbl_routers r ON c.router_id = r.id
WHERE c.status = 'active'
AND c.expiry_date < DATE_ADD(NOW(), INTERVAL 30 DAY)
ORDER BY c.expiry_date ASC;
```

### Get router connection statistics
```sql
SELECT 
    r.name,
    r.ovpn_status,
    COUNT(l.id) as total_logs,
    SUM(l.bytes_sent + l.bytes_received) as total_bytes,
    MAX(l.created_at) as last_log
FROM tbl_routers r
LEFT JOIN tbl_vpn_connection_logs l ON r.id = l.router_id
WHERE r.connection_type = 'remote'
GROUP BY r.id;
```

### Audit trail for specific router
```sql
SELECT 
    a.created_at,
    a.action,
    u.username as admin,
    a.status,
    a.details
FROM tbl_vpn_audit_log a
JOIN tbl_users u ON a.admin_id = u.id
WHERE a.router_id = ?
ORDER BY a.created_at DESC;
```

## Performance Considerations

- Indexes on `router_id` and `created_at` optimize common queries
- Connection logs should be archived/purged periodically
- Consider partitioning `tbl_vpn_connection_logs` by date for large deployments
- Use `EXPLAIN` to verify query performance

## Security Notes

- `vpn_password_hash` uses Argon2ID hashing (never store plain passwords)
- `ip_address` in audit log helps track administrative actions
- Foreign key constraints maintain referential integrity
- All timestamps use server timezone (ensure consistency)

---

**Schema Version:** 2025.12.3.vpn  
**Last Updated:** December 3, 2025
