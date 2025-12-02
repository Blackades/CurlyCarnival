#!/bin/bash
#
# restart_openvpn.sh - Restart OpenVPN service with configuration testing
#
# Exit codes:
#   0 = Success
#   1 = Configuration test failed
#   2 = Restart failed
#
# Output: SUCCESS or ERROR:message with status details

set -e

# Configuration
OPENVPN_SERVICE="${OPENVPN_SERVICE:-openvpn@server}"
OPENVPN_CONFIG="${OPENVPN_CONFIG:-/etc/openvpn/server.conf}"
LOG_FILE="${LOG_FILE:-/var/log/phpnuxbill/vpn-service.log}"

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Function to get service status
get_service_status() {
    if systemctl is-active --quiet "$OPENVPN_SERVICE"; then
        echo "running"
    else
        echo "stopped"
    fi
}

# Test OpenVPN configuration
log_message "Testing OpenVPN configuration..."
if ! openvpn --config "$OPENVPN_CONFIG" --test-crypto 2>&1 | head -20; then
    echo "ERROR:Configuration test failed"
    log_message "OpenVPN configuration test failed"
    exit 1
fi

# Get current status
BEFORE_STATUS=$(get_service_status)
log_message "Service status before restart: $BEFORE_STATUS"

# Restart OpenVPN service
log_message "Restarting OpenVPN service..."
if ! systemctl restart "$OPENVPN_SERVICE" 2>&1; then
    echo "ERROR:Failed to restart OpenVPN service"
    log_message "Failed to restart OpenVPN service"
    exit 2
fi

# Wait for service to start
sleep 2

# Verify service is running
AFTER_STATUS=$(get_service_status)
log_message "Service status after restart: $AFTER_STATUS"

if [ "$AFTER_STATUS" != "running" ]; then
    # Get service status details
    STATUS_OUTPUT=$(systemctl status "$OPENVPN_SERVICE" 2>&1 || true)
    echo "ERROR:Service failed to start. Status: $STATUS_OUTPUT"
    log_message "OpenVPN service failed to start after restart"
    exit 2
fi

log_message "OpenVPN service restarted successfully"
echo "SUCCESS:OpenVPN service restarted successfully"
exit 0
