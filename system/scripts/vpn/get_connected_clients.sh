#!/bin/bash
#
# get_connected_clients.sh - List connected VPN clients
#
# Exit codes:
#   0 = Success
#   1 = Status file not found or no clients
#   2 = Error reading status
#
# Output: JSON array of connected clients

set -e

# Configuration
OPENVPN_STATUS_FILE="${OPENVPN_STATUS_FILE:-/var/log/openvpn/openvpn-status.log}"
OPENVPN_STATUS_FILE_ALT="/etc/openvpn/openvpn-status.log"

# Find status file
if [ -f "$OPENVPN_STATUS_FILE" ]; then
    STATUS_FILE="$OPENVPN_STATUS_FILE"
elif [ -f "$OPENVPN_STATUS_FILE_ALT" ]; then
    STATUS_FILE="$OPENVPN_STATUS_FILE_ALT"
else
    echo '{"error": "Status file not found", "clients": []}'
    exit 1
fi

# Check if file is readable
if [ ! -r "$STATUS_FILE" ]; then
    echo '{"error": "Cannot read status file", "clients": []}'
    exit 2
fi

# Parse status file and extract client information
# OpenVPN status file format:
# Common Name,Real Address,Bytes Received,Bytes Sent,Connected Since
CLIENTS='[]'

# Check if file has client routing table section
if grep -q "^CLIENT_LIST" "$STATUS_FILE" 2>/dev/null; then
    # OpenVPN 2.4+ format with CLIENT_LIST entries
    CLIENTS=$(awk -F',' '
    BEGIN { 
        printf "[" 
        first = 1
    }
    /^CLIENT_LIST/ {
        if (!first) printf ","
        first = 0
        printf "{\"common_name\":\"%s\",\"real_address\":\"%s\",\"virtual_address\":\"%s\",\"bytes_received\":\"%s\",\"bytes_sent\":\"%s\",\"connected_since\":\"%s\"}", $2, $3, $4, $5, $6, $8
    }
    END { 
        printf "]" 
    }
    ' "$STATUS_FILE")
elif grep -q "^Common Name," "$STATUS_FILE" 2>/dev/null; then
    # Older OpenVPN format with CSV-style client list
    CLIENTS=$(awk -F',' '
    BEGIN { 
        printf "[" 
        first = 1
        in_client_section = 0
    }
    /^Common Name,Real Address/ {
        in_client_section = 1
        next
    }
    /^ROUTING TABLE/ || /^Virtual Address/ {
        in_client_section = 0
    }
    in_client_section && NF >= 5 && $1 !~ /^Common Name/ {
        if (!first) printf ","
        first = 0
        printf "{\"common_name\":\"%s\",\"real_address\":\"%s\",\"bytes_received\":\"%s\",\"bytes_sent\":\"%s\",\"connected_since\":\"%s\"}", $1, $2, $3, $4, $5
    }
    END { 
        printf "]" 
    }
    ' "$STATUS_FILE")
fi

# Output JSON
cat << EOF
{
  "timestamp": "$(date '+%Y-%m-%d %H:%M:%S')",
  "status_file": "$STATUS_FILE",
  "clients": $CLIENTS
}
EOF

# Exit with success if we have clients, otherwise exit 1
if [ "$CLIENTS" = "[]" ]; then
    exit 1
else
    exit 0
fi
