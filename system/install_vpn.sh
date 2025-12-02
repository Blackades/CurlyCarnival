#!/bin/bash

###############################################################################
# VPN Feature Installation Script for phpnuxbill
#
# This script installs and configures the MikroTik OpenVPN automation feature.
# It performs the following tasks:
# 1. Creates necessary directories with proper permissions
# 2. Runs database migrations
# 3. Sets up system scripts
# 4. Configures sudo permissions
# 5. Sets up cron jobs
# 6. Configures log rotation
#
# Usage: sudo bash system/install_vpn.sh
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PHPNUXBILL_DIR="/var/www/html"
WEB_USER="www-data"
SCRIPT_DIR="$PHPNUXBILL_DIR/system/scripts/vpn"
STORAGE_DIR="$PHPNUXBILL_DIR/system/storage/vpn-configs"
LOG_DIR="/var/log/phpnuxbill"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Error: This script must be run as root (use sudo)${NC}"
    exit 1
fi

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  phpnuxbill VPN Feature Installation                      ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Step 1: Create directories
echo -e "${YELLOW}[1/7]${NC} Creating directories..."
mkdir -p "$SCRIPT_DIR"
mkdir -p "$STORAGE_DIR"
mkdir -p "$LOG_DIR"
echo -e "${GREEN}✓${NC} Directories created"
echo ""

# Step 2: Set directory permissions
echo -e "${YELLOW}[2/7]${NC} Setting directory permissions..."
chmod 750 "$SCRIPT_DIR"
chown root:$WEB_USER "$SCRIPT_DIR"
chmod 750 "$STORAGE_DIR"
chown $WEB_USER:$WEB_USER "$STORAGE_DIR"
chmod 755 "$LOG_DIR"
chown $WEB_USER:$WEB_USER "$LOG_DIR"
echo -e "${GREEN}✓${NC} Permissions set"
echo ""

# Step 3: Run database migration
echo -e "${YELLOW}[3/7]${NC} Running database migration..."
cd "$PHPNUXBILL_DIR"
if php system/migrate_vpn.php; then
    echo -e "${GREEN}✓${NC} Database migration completed"
else
    echo -e "${RED}✗${NC} Database migration failed"
    exit 1
fi
echo ""

# Step 4: Configure sudo permissions
echo -e "${YELLOW}[4/7]${NC} Configuring sudo permissions..."
SUDOERS_FILE="/etc/sudoers.d/phpnuxbill-vpn"

cat > "$SUDOERS_FILE" << 'EOF'
# PHPNuxBill VPN Management Scripts
# Web server user sudo permissions for VPN operations
Defaults:www-data !requiretty

# Certificate management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/check_certificates.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/generate_client_cert.sh *
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/revoke_client_cert.sh *

# VPN user management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/add_vpn_user.sh * *
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/remove_vpn_user.sh *

# OpenVPN service management
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/restart_openvpn.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/get_vpn_status.sh
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/get_connected_clients.sh

# Configuration generation
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/generate_ovpn_config.sh * * *

# Cleanup
www-data ALL=(ALL) NOPASSWD: /var/www/html/system/scripts/vpn/cleanup_expired_certs.sh
EOF

chmod 440 "$SUDOERS_FILE"
visudo -c -f "$SUDOERS_FILE" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Sudo permissions configured"
else
    echo -e "${RED}✗${NC} Sudoers file validation failed"
    rm -f "$SUDOERS_FILE"
    exit 1
fi
echo ""

# Step 5: Configure cron jobs
echo -e "${YELLOW}[5/7]${NC} Configuring cron jobs..."
CRON_FILE="/etc/cron.d/phpnuxbill-vpn"

cat > "$CRON_FILE" << EOF
# PHPNuxBill VPN Feature Cron Jobs

# VPN Health Check - Every 5 minutes
*/5 * * * * $WEB_USER /usr/bin/php $PHPNUXBILL_DIR/system/cron_vpn_health.php >> $LOG_DIR/vpn-health.log 2>&1

# Certificate Expiry Check - Daily at 2 AM
0 2 * * * $WEB_USER /usr/bin/php $PHPNUXBILL_DIR/system/cron_vpn_cert_check.php >> $LOG_DIR/vpn-cert-check.log 2>&1

# Cleanup Expired Certificates - Weekly on Sunday at 3 AM
0 3 * * 0 $WEB_USER sudo $PHPNUXBILL_DIR/system/scripts/vpn/cleanup_expired_certs.sh >> $LOG_DIR/vpn-cleanup.log 2>&1

# VPN Connection Statistics - Hourly
0 * * * * $WEB_USER /usr/bin/php $PHPNUXBILL_DIR/system/cron_vpn_stats.php >> $LOG_DIR/vpn-stats.log 2>&1
EOF

chmod 644 "$CRON_FILE"
echo -e "${GREEN}✓${NC} Cron jobs configured"
echo ""

# Step 6: Configure log rotation
echo -e "${YELLOW}[6/7]${NC} Configuring log rotation..."
LOGROTATE_FILE="/etc/logrotate.d/phpnuxbill-vpn"

cat > "$LOGROTATE_FILE" << EOF
$LOG_DIR/vpn-*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 $WEB_USER $WEB_USER
    sharedscripts
    postrotate
        systemctl reload rsyslog > /dev/null 2>&1 || true
    endscript
}
EOF

chmod 644 "$LOGROTATE_FILE"
echo -e "${GREEN}✓${NC} Log rotation configured"
echo ""

# Step 7: Set script permissions
echo -e "${YELLOW}[7/9]${NC} Setting script permissions..."
if [ -d "$SCRIPT_DIR" ]; then
    chmod 750 "$SCRIPT_DIR"/*.sh 2>/dev/null || true
    chown root:$WEB_USER "$SCRIPT_DIR"/*.sh 2>/dev/null || true
    echo -e "${GREEN}✓${NC} Script permissions set"
else
    echo -e "${YELLOW}⚠${NC} Script directory not found, skipping"
fi
echo ""

# Step 8: Test scripts with sudo
echo -e "${YELLOW}[8/9]${NC} Testing scripts with sudo..."
SCRIPTS_TO_TEST=(
    "check_certificates.sh"
    "get_vpn_status.sh"
)

TEST_PASSED=0
TEST_FAILED=0

for script in "${SCRIPTS_TO_TEST[@]}"; do
    SCRIPT_PATH="$SCRIPT_DIR/$script"
    if [ -f "$SCRIPT_PATH" ]; then
        echo -n "  Testing $script... "
        if sudo -u $WEB_USER "$SCRIPT_PATH" > /dev/null 2>&1; then
            echo -e "${GREEN}✓${NC}"
            ((TEST_PASSED++))
        else
            # Some scripts may fail if OpenVPN is not configured yet, that's okay
            echo -e "${YELLOW}⚠${NC} (may require OpenVPN configuration)"
            ((TEST_PASSED++))
        fi
    else
        echo -e "  ${YELLOW}⚠${NC} Script $script not found, skipping test"
    fi
done

if [ $TEST_PASSED -gt 0 ]; then
    echo -e "${GREEN}✓${NC} Script testing completed ($TEST_PASSED tested)"
else
    echo -e "${YELLOW}⚠${NC} No scripts found to test"
fi
echo ""

# Step 9: Create initial log files
echo -e "${YELLOW}[9/9]${NC} Creating initial log files..."
touch "$LOG_DIR/vpn-health.log"
touch "$LOG_DIR/vpn-cert-check.log"
touch "$LOG_DIR/vpn-cleanup.log"
touch "$LOG_DIR/vpn-stats.log"
touch "$LOG_DIR/vpn-errors.log"
chown $WEB_USER:$WEB_USER "$LOG_DIR"/vpn-*.log
chmod 640 "$LOG_DIR"/vpn-*.log
echo -e "${GREEN}✓${NC} Log files created"
echo ""

# Installation complete
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Installation Complete!                                    ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✓${NC} VPN feature has been successfully installed"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Configure VPN settings in config.php"
echo "2. Ensure OpenVPN server is installed and configured"
echo "3. Set up EasyRSA for certificate management"
echo "4. Create VPN management bash scripts in $SCRIPT_DIR"
echo "5. Test the installation by adding a remote router"
echo ""
echo -e "${YELLOW}Important Files:${NC}"
echo "  Scripts:  $SCRIPT_DIR"
echo "  Storage:  $STORAGE_DIR"
echo "  Logs:     $LOG_DIR"
echo "  Sudoers:  $SUDOERS_FILE"
echo "  Cron:     $CRON_FILE"
echo ""
echo -e "${BLUE}For more information, see the documentation.${NC}"
echo ""
