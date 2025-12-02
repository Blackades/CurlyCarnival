#!/bin/bash
#
# check_certificates.sh - Validate CA and server certificate expiry
#
# Exit codes:
#   0 = Valid certificates
#   1 = CA certificate missing
#   2 = CA certificate expiring soon
#   3 = Server certificate missing
#   4 = Server certificate expiring soon
#
# Output format: STATUS:days_remaining or ERROR:message

set -e

# Configuration
EASYRSA_DIR="${EASYRSA_DIR:-/etc/openvpn/easy-rsa}"
PKI_DIR="${EASYRSA_DIR}/pki"
CA_CERT="${PKI_DIR}/ca.crt"
SERVER_CERT="${PKI_DIR}/issued/server.crt"
WARNING_DAYS=30

# Function to get certificate expiry date
get_cert_expiry() {
    local cert_file="$1"
    openssl x509 -enddate -noout -in "$cert_file" | cut -d= -f2
}

# Function to calculate days until expiry
days_until_expiry() {
    local cert_file="$1"
    local expiry_date=$(get_cert_expiry "$cert_file")
    local expiry_epoch=$(date -d "$expiry_date" +%s)
    local current_epoch=$(date +%s)
    local days_remaining=$(( ($expiry_epoch - $current_epoch) / 86400 ))
    echo "$days_remaining"
}

# Check if CA certificate exists
if [ ! -f "$CA_CERT" ]; then
    echo "ERROR:CA certificate not found at $CA_CERT"
    exit 1
fi

# Check CA certificate expiry
CA_DAYS=$(days_until_expiry "$CA_CERT")
if [ "$CA_DAYS" -lt 0 ]; then
    echo "ERROR:CA certificate has expired"
    exit 1
elif [ "$CA_DAYS" -lt "$WARNING_DAYS" ]; then
    echo "CA_EXPIRING:$CA_DAYS"
    exit 2
fi

# Check if server certificate exists
if [ ! -f "$SERVER_CERT" ]; then
    echo "ERROR:Server certificate not found at $SERVER_CERT"
    exit 3
fi

# Check server certificate expiry
SERVER_DAYS=$(days_until_expiry "$SERVER_CERT")
if [ "$SERVER_DAYS" -lt 0 ]; then
    echo "ERROR:Server certificate has expired"
    exit 3
elif [ "$SERVER_DAYS" -lt "$WARNING_DAYS" ]; then
    echo "SERVER_CERT_EXPIRING:$SERVER_DAYS"
    exit 4
fi

# All checks passed
echo "VALID:$CA_DAYS"
exit 0
