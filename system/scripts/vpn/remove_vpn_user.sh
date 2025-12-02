#!/bin/bash
#
# remove_vpn_user.sh - Remove VPN user from OpenVPN authentication script
#
# Usage: remove_vpn_user.sh <username>
#
# Exit codes:
#   0 = Success
#   1 = User not found
#   2 = Script error
#
# Output: SUCCESS or ERROR:message

set -e

# Configuration
AUTH_SCRIPT="${AUTH_SCRIPT:-/etc/openvpn/check-auth.sh}"
BACKUP_DIR="${BACKUP_DIR:-/etc/openvpn/backups}"
LOG_FILE="${LOG_FILE:-/var/log/phpnuxbill/vpn-user-management.log}"

# Validate arguments
if [ $# -ne 1 ]; then
    echo "ERROR:Invalid arguments. Usage: remove_vpn_user.sh <username>"
    exit 2
fi

USERNAME="$1"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Check if authentication script exists
if [ ! -f "$AUTH_SCRIPT" ]; then
    echo "ERROR:Authentication script not found"
    log_message "Failed to remove user $USERNAME - authentication script not found"
    exit 2
fi

# Check if user exists
if ! grep -q "^\[ \"\$USERNAME\" = \"$USERNAME\" \]" "$AUTH_SCRIPT"; then
    echo "ERROR:User $USERNAME not found"
    log_message "Failed to remove user $USERNAME - user not found"
    exit 1
fi

# Create timestamped backup
BACKUP_FILE="${BACKUP_DIR}/check-auth.sh.$(date +%Y%m%d_%H%M%S)"
if ! cp "$AUTH_SCRIPT" "$BACKUP_FILE"; then
    echo "ERROR:Failed to create backup"
    log_message "Failed to create backup for removing user $USERNAME"
    exit 2
fi

# Create temporary file without the user
TEMP_FILE=$(mktemp)

# Remove user lines (comment line and authentication line)
awk -v user="$USERNAME" '
/^# User: / && $3 == user { next }
/^\[ "\$USERNAME" = "'"$USERNAME"'" \]/ { next }
/^$/ && prev_skip { next }
{ print; prev_skip = 0 }
' "$AUTH_SCRIPT" > "$TEMP_FILE"

# Replace original script with modified version
if ! mv "$TEMP_FILE" "$AUTH_SCRIPT"; then
    echo "ERROR:Failed to update authentication script"
    log_message "Failed to update authentication script for removing user $USERNAME"
    rm -f "$TEMP_FILE"
    exit 2
fi

# Set proper permissions
chmod +x "$AUTH_SCRIPT"
chown root:root "$AUTH_SCRIPT"

log_message "Successfully removed VPN user: $USERNAME"
echo "SUCCESS:User $USERNAME removed successfully"
exit 0
