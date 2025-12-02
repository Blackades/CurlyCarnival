#!/bin/bash
#
# set_permissions.sh - Set proper permissions and ownership on VPN scripts
#
# This script should be run as root to set proper permissions
# Usage: sudo ./set_permissions.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "Setting permissions on VPN management scripts..."

# Set ownership to root:www-data
chown root:www-data "$SCRIPT_DIR"/*.sh

# Set permissions to 750 (rwxr-x---)
chmod 750 "$SCRIPT_DIR"/*.sh

echo "Permissions set successfully:"
ls -la "$SCRIPT_DIR"/*.sh

echo ""
echo "Done! All scripts are now owned by root:www-data with 750 permissions."
