#!/bin/bash
#
# revoke_client_cert.sh - Revoke client certificate
#
# Usage: revoke_client_cert.sh <client_name>
#
# Exit codes:
#   0 = Success
#   1 = Revocation failed
#   2 = Invalid arguments
#
# Output: SUCCESS or ERROR:message

set -e

# Configuration
EASYRSA_DIR="${EASYRSA_DIR:-/etc/openvpn/easy-rsa}"
PKI_DIR="${EASYRSA_DIR}/pki"
LOG_FILE="${LOG_FILE:-/var/log/phpnuxbill/vpn-cert-management.log}"

# Validate arguments
if [ $# -ne 1 ]; then
    echo "ERROR:Invalid arguments. Usage: revoke_client_cert.sh <client_name>"
    exit 2
fi

CLIENT_NAME="$1"

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Function to log messages
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Change to EasyRSA directory
cd "$EASYRSA_DIR" || {
    echo "ERROR:EasyRSA directory not found at $EASYRSA_DIR"
    log_message "Failed to revoke certificate for $CLIENT_NAME - EasyRSA directory not found"
    exit 1
}

# Check if certificate exists
if [ ! -f "$PKI_DIR/issued/${CLIENT_NAME}.crt" ]; then
    echo "ERROR:Certificate for $CLIENT_NAME not found"
    log_message "Failed to revoke certificate for $CLIENT_NAME - certificate not found"
    exit 1
fi

# Revoke the certificate
if ! ./easyrsa --batch revoke "$CLIENT_NAME" 2>&1; then
    echo "ERROR:Failed to revoke certificate for $CLIENT_NAME"
    log_message "Failed to revoke certificate for $CLIENT_NAME"
    exit 1
fi

# Generate updated CRL (Certificate Revocation List)
if ! ./easyrsa gen-crl 2>&1; then
    echo "ERROR:Failed to generate CRL after revoking $CLIENT_NAME"
    log_message "Failed to generate CRL after revoking $CLIENT_NAME"
    exit 1
fi

# Copy CRL to OpenVPN directory
if [ -f "$PKI_DIR/crl.pem" ]; then
    cp "$PKI_DIR/crl.pem" /etc/openvpn/crl.pem
    chmod 644 /etc/openvpn/crl.pem
fi

log_message "Successfully revoked certificate for $CLIENT_NAME"
echo "SUCCESS:Certificate for $CLIENT_NAME revoked successfully"
exit 0
