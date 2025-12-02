#!/bin/bash
#
# add_vpn_user.sh - Add VPN user to OpenVPN authentication script
#
# Usage: add_vpn_user.sh <username> <password>
#
# Exit codes:
#   0 = Success
#   1 = User already exists
#   2 = Script error (backup failed, write failed, etc.)
#
# Output: SUCCESS or ERROR:message

set -e

# Configuration
AUTH_SCRIPT="${AUTH_SCRIPT:-/etc/openvpn/check-auth.sh}"
BACKUP_DIR="${BACKUP_DIR:-/etc/openvpn/backups}"
LOG_FILE="${LOG_FILE:-/var/log/phpnuxbill/vpn-user-management.log}"

# Validate arguments
if [ $# -ne 2 ]; then
    echo "ERROR:Invalid arguments. Usage: add_vpn_user.sh <username> <password>"
    exit 2
fi

USERNAME="$1"
PASSWORD="$2"

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
    # Create new authentication script
    cat > "$AUTH_SCRIPT" << 'EOF'
#!/bin/bash
# OpenVPN Authentication Script
# This script validates username and password for OpenVPN connections

USERNAME="$1"
PASSWORD="$2"

# VPN Users - DO NOT EDIT THIS LINE
# phpnuxbill managed users below

# Default deny
exit 1
EOF
    chmod +x "$AUTH_SCRIPT"
    log_message "Created new authentication script at $AUTH_SCRIPT"
fi

# Check if user already exists
if grep -q "^\[ \"\$USERNAME\" = \"$USERNAME\" \]" "$AUTH_SCRIPT"; then
    echo "ERROR:User $USERNAME already exists"
    log_message "Failed to add user $USERNAME - already exists"
    exit 1
fi

# Create timestamped backup
BACKUP_FILE="${BACKUP_DIR}/check-auth.sh.$(date +%Y%m%d_%H%M%S)"
if ! cp "$AUTH_SCRIPT" "$BACKUP_FILE"; then
    echo "ERROR:Failed to create backup"
    log_message "Failed to create backup for adding user $USERNAME"
    exit 2
fi

# Create temporary file with new user
TEMP_FILE=$(mktemp)

# Read the script and insert new user before the final "exit 1"
awk -v user="$USERNAME" -v pass="$PASSWORD" '
/^# Default deny/ {
    print "# User: " user
    print "[ \"$USERNAME\" = \"" user "\" ] && [ \"$PASSWORD\" = \"" pass "\" ] && exit 0"
    print ""
}
{ print }
' "$AUTH_SCRIPT" > "$TEMP_FILE"

# Replace original script with modified version
if ! mv "$TEMP_FILE" "$AUTH_SCRIPT"; then
    echo "ERROR:Failed to update authentication script"
    log_message "Failed to update authentication script for user $USERNAME"
    rm -f "$TEMP_FILE"
    exit 2
fi

# Set proper permissions
chmod +x "$AUTH_SCRIPT"
chown root:root "$AUTH_SCRIPT"

log_message "Successfully added VPN user: $USERNAME"
echo "SUCCESS:User $USERNAME added successfully"
exit 0
