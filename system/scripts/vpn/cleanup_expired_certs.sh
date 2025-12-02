#!/bin/bash
#
# cleanup_expired_certs.sh - Remove expired certificate files
#
# Exit codes:
#   0 = Success
#   1 = Error during cleanup
#
# Output: Summary of cleanup operations

set -e

# Configuration
EASYRSA_DIR="${EASYRSA_DIR:-/etc/openvpn/easy-rsa}"
PKI_DIR="${EASYRSA_DIR}/pki"
ARCHIVE_DIR="${ARCHIVE_DIR:-/etc/openvpn/expired-certs}"
LOG_FILE="${LOG_FILE:-/var/log/phpnuxbill/vpn-cleanup.log}"

# Create directories if they don't exist
mkdir -p "$ARCHIVE_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Function to check if certificate is expired
is_cert_expired() {
    local cert_file="$1"
    local expiry_date=$(openssl x509 -enddate -noout -in "$cert_file" 2>/dev/null | cut -d= -f2)
    local expiry_epoch=$(date -d "$expiry_date" +%s 2>/dev/null || echo "0")
    local current_epoch=$(date +%s)
    
    if [ "$expiry_epoch" -lt "$current_epoch" ]; then
        return 0  # Expired
    else
        return 1  # Not expired
    fi
}

# Counters
EXPIRED_COUNT=0
ARCHIVED_COUNT=0
ERROR_COUNT=0

log_message "Starting certificate cleanup process"

# Check if PKI directory exists
if [ ! -d "$PKI_DIR/issued" ]; then
    echo "ERROR:PKI directory not found at $PKI_DIR"
    log_message "PKI directory not found - cleanup aborted"
    exit 1
fi

# Process all issued certificates
for cert_file in "$PKI_DIR/issued"/*.crt; do
    # Skip if no certificates found
    [ -e "$cert_file" ] || continue
    
    # Get certificate name
    cert_name=$(basename "$cert_file" .crt)
    
    # Skip CA and server certificates
    if [ "$cert_name" = "ca" ] || [ "$cert_name" = "server" ]; then
        continue
    fi
    
    # Check if certificate is expired
    if is_cert_expired "$cert_file"; then
        EXPIRED_COUNT=$((EXPIRED_COUNT + 1))
        log_message "Found expired certificate: $cert_name"
        
        # Create archive subdirectory with date
        ARCHIVE_SUBDIR="$ARCHIVE_DIR/$(date +%Y%m%d)"
        mkdir -p "$ARCHIVE_SUBDIR"
        
        # Archive certificate file
        if [ -f "$cert_file" ]; then
            if cp "$cert_file" "$ARCHIVE_SUBDIR/${cert_name}.crt"; then
                rm -f "$cert_file"
                log_message "Archived and removed: $cert_file"
            else
                log_message "ERROR: Failed to archive $cert_file"
                ERROR_COUNT=$((ERROR_COUNT + 1))
                continue
            fi
        fi
        
        # Archive key file if exists
        key_file="$PKI_DIR/private/${cert_name}.key"
        if [ -f "$key_file" ]; then
            if cp "$key_file" "$ARCHIVE_SUBDIR/${cert_name}.key"; then
                rm -f "$key_file"
                log_message "Archived and removed: $key_file"
            else
                log_message "ERROR: Failed to archive $key_file"
                ERROR_COUNT=$((ERROR_COUNT + 1))
            fi
        fi
        
        # Archive OVPN config if exists
        ovpn_file="/var/www/html/system/storage/vpn-configs/${cert_name}.ovpn"
        if [ -f "$ovpn_file" ]; then
            if cp "$ovpn_file" "$ARCHIVE_SUBDIR/${cert_name}.ovpn"; then
                rm -f "$ovpn_file"
                log_message "Archived and removed: $ovpn_file"
            else
                log_message "ERROR: Failed to archive $ovpn_file"
                ERROR_COUNT=$((ERROR_COUNT + 1))
            fi
        fi
        
        ARCHIVED_COUNT=$((ARCHIVED_COUNT + 1))
    fi
done

# Generate summary
log_message "Cleanup completed: $EXPIRED_COUNT expired certificates found, $ARCHIVED_COUNT archived, $ERROR_COUNT errors"

cat << EOF
{
  "timestamp": "$(date '+%Y-%m-%d %H:%M:%S')",
  "expired_found": $EXPIRED_COUNT,
  "archived": $ARCHIVED_COUNT,
  "errors": $ERROR_COUNT,
  "archive_location": "$ARCHIVE_DIR"
}
EOF

if [ "$ERROR_COUNT" -gt 0 ]; then
    exit 1
else
    exit 0
fi
