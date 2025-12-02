#!/bin/bash
#
# get_vpn_status.sh - Check OpenVPN service status
#
# Exit codes:
#   0 = Service running
#   1 = Service stopped
#   2 = Service failed/error
#
# Output: JSON format with status details

set -e

# Configuration
OPENVPN_SERVICE="${OPENVPN_SERVICE:-openvpn@server}"

# Function to get service status
get_service_status() {
    if systemctl is-active --quiet "$OPENVPN_SERVICE"; then
        echo "running"
    elif systemctl is-failed --quiet "$OPENVPN_SERVICE"; then
        echo "failed"
    else
        echo "stopped"
    fi
}

# Get detailed status
STATUS=$(get_service_status)
UPTIME=""
PID=""

if [ "$STATUS" = "running" ]; then
    # Get service PID
    PID=$(systemctl show -p MainPID --value "$OPENVPN_SERVICE")
    
    # Get uptime
    START_TIME=$(systemctl show -p ActiveEnterTimestamp --value "$OPENVPN_SERVICE")
    if [ -n "$START_TIME" ]; then
        START_EPOCH=$(date -d "$START_TIME" +%s 2>/dev/null || echo "0")
        CURRENT_EPOCH=$(date +%s)
        UPTIME_SECONDS=$((CURRENT_EPOCH - START_EPOCH))
        UPTIME="${UPTIME_SECONDS}s"
    fi
fi

# Output JSON
cat << EOF
{
  "status": "$STATUS",
  "service": "$OPENVPN_SERVICE",
  "pid": "$PID",
  "uptime": "$UPTIME",
  "timestamp": "$(date '+%Y-%m-%d %H:%M:%S')"
}
EOF

# Exit with appropriate code
case "$STATUS" in
    running)
        exit 0
        ;;
    stopped)
        exit 1
        ;;
    failed)
        exit 2
        ;;
    *)
        exit 2
        ;;
esac
