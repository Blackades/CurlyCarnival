#!/bin/bash
#
# generate_client_cert.sh - Generate client certificate using EasyRSA
#
# Usage: generate_client_cert.sh <client_name> [validity_days]
#
# Exit codes:
#   0 = Success
#   1 = Certificate generation failed
#   2 = Invalid arguments
#
# Output: SUCCESS:output_path or ERROR:message

set -e

# Configuration
EASYRSA_DIR="${EASYRSA_DIR:-/etc/openvpn/easy-rsa}"
PKI_DIR="${EASYRSA_DIR}/pki"
DEFAULT_VALIDITY_DAYS=365

# Validate arguments
if [ $# -lt 1 ]; then
    echo "ERROR:Invalid arguments. Usage: generate_client_cert.sh <client_name> [validity_days]"
    exit 2
fi

CLIENT_NAME="$1"
VALIDITY_DAYS="${2:-$DEFAULT_VALIDITY_DAYS}"

# Validate client name (alphanumeric, underscore, hyphen only)
if ! echo "$CLIENT_NAME" | grep -qE '^[a-zA-Z0-9_-]+$'; then
    echo "ERROR:Invalid client name. Use only alphanumeric characters, underscore, and hyphen"
    exit 2
fi

# Change to EasyRSA directory
cd "$EASYRSA_DIR" || {
    echo "ERROR:EasyRSA directory not found at $EASYRSA_DIR"
    exit 1
}

# Check if EasyRSA is initialized
if [ ! -d "$PKI_DIR" ]; then
    echo "ERROR:EasyRSA PKI not initialized. Run './easyrsa init-pki' first"
    exit 1
fi

# Check if CA exists
if [ ! -f "$PKI_DIR/ca.crt" ]; then
    echo "ERROR:CA certificate not found. Build CA first"
    exit 1
fi

# Check if client certificate already exists
if [ -f "$PKI_DIR/issued/${CLIENT_NAME}.crt" ]; then
    echo "ERROR:Certificate for $CLIENT_NAME already exists"
    exit 1
fi

# Generate client certificate without password
export EASYRSA_CERT_EXPIRE="$VALIDITY_DAYS"
if ! ./easyrsa --batch build-client-full "$CLIENT_NAME" nopass 2>&1; then
    echo "ERROR:Failed to generate certificate for $CLIENT_NAME"
    exit 1
fi

# Verify certificate was created
if [ ! -f "$PKI_DIR/issued/${CLIENT_NAME}.crt" ] || [ ! -f "$PKI_DIR/private/${CLIENT_NAME}.key" ]; then
    echo "ERROR:Certificate files not found after generation"
    exit 1
fi

# Set proper permissions
chmod 644 "$PKI_DIR/issued/${CLIENT_NAME}.crt"
chmod 600 "$PKI_DIR/private/${CLIENT_NAME}.key"

# Output success with certificate path
echo "SUCCESS:$PKI_DIR/issued/${CLIENT_NAME}.crt"
exit 0
