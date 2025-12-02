#!/bin/bash
#
# generate_ovpn_config.sh - Generate OVPN configuration file with embedded certificates
#
# Usage: generate_ovpn_config.sh <client_name> <server_ip> <server_port> [protocol] [output_dir]
#
# Exit codes:
#   0 = Success
#   1 = File creation failed
#   2 = Invalid arguments
#
# Output: SUCCESS:file_path or ERROR:message

set -e

# Configuration
EASYRSA_DIR="${EASYRSA_DIR:-/etc/openvpn/easy-rsa}"
PKI_DIR="${EASYRSA_DIR}/pki"
DEFAULT_OUTPUT_DIR="/var/www/html/system/storage/vpn-configs"
DEFAULT_PROTOCOL="tcp"

# Validate arguments
if [ $# -lt 3 ]; then
    echo "ERROR:Invalid arguments. Usage: generate_ovpn_config.sh <client_name> <server_ip> <server_port> [protocol] [output_dir]"
    exit 2
fi

CLIENT_NAME="$1"
SERVER_IP="$2"
SERVER_PORT="$3"
PROTOCOL="${4:-$DEFAULT_PROTOCOL}"
OUTPUT_DIR="${5:-$DEFAULT_OUTPUT_DIR}"

# Create output directory if it doesn't exist
mkdir -p "$OUTPUT_DIR"

# Validate certificate files exist
CA_CERT="$PKI_DIR/ca.crt"
CLIENT_CERT="$PKI_DIR/issued/${CLIENT_NAME}.crt"
CLIENT_KEY="$PKI_DIR/private/${CLIENT_NAME}.key"

if [ ! -f "$CA_CERT" ]; then
    echo "ERROR:CA certificate not found at $CA_CERT"
    exit 1
fi

if [ ! -f "$CLIENT_CERT" ]; then
    echo "ERROR:Client certificate not found at $CLIENT_CERT"
    exit 1
fi

if [ ! -f "$CLIENT_KEY" ]; then
    echo "ERROR:Client key not found at $CLIENT_KEY"
    exit 1
fi

# Output file path
OVPN_FILE="$OUTPUT_DIR/${CLIENT_NAME}.ovpn"

# Generate OVPN configuration file
cat > "$OVPN_FILE" << EOF
# OpenVPN Client Configuration
# Generated: $(date '+%Y-%m-%d %H:%M:%S')
# Client: $CLIENT_NAME

client
dev tun
proto $PROTOCOL
remote $SERVER_IP $SERVER_PORT
resolv-retry infinite
nobind
persist-key
persist-tun
remote-cert-tls server
cipher AES-256-GCM
auth SHA256
verb 3
auth-user-pass

# Embedded Certificates
<ca>
$(cat "$CA_CERT")
</ca>

<cert>
$(cat "$CLIENT_CERT")
</cert>

<key>
$(cat "$CLIENT_KEY")
</key>
EOF

# Set proper permissions
chmod 600 "$OVPN_FILE"
chown www-data:www-data "$OVPN_FILE" 2>/dev/null || true

# Verify file was created
if [ ! -f "$OVPN_FILE" ]; then
    echo "ERROR:Failed to create OVPN file"
    exit 1
fi

echo "SUCCESS:$OVPN_FILE"
exit 0
