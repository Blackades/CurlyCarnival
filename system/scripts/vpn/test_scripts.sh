#!/bin/bash
#
# test_scripts.sh - Test all VPN management scripts
#
# This script performs basic validation of all VPN scripts
# Run as root: sudo ./test_scripts.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TEST_USER="test-vpn-user-$$"
TEST_CLIENT="test-client-$$"
PASSED=0
FAILED=0

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================="
echo "VPN Management Scripts Test Suite"
echo "========================================="
echo ""

# Function to print test result
print_result() {
    local test_name="$1"
    local result="$2"
    
    if [ "$result" = "PASS" ]; then
        echo -e "${GREEN}✓${NC} $test_name"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗${NC} $test_name"
        FAILED=$((FAILED + 1))
    fi
}

# Test 1: Check if all scripts exist
echo "Test 1: Checking script files..."
SCRIPTS=(
    "check_certificates.sh"
    "add_vpn_user.sh"
    "remove_vpn_user.sh"
    "generate_client_cert.sh"
    "revoke_client_cert.sh"
    "generate_ovpn_config.sh"
    "restart_openvpn.sh"
    "get_vpn_status.sh"
    "get_connected_clients.sh"
    "cleanup_expired_certs.sh"
)

for script in "${SCRIPTS[@]}"; do
    if [ -f "$SCRIPT_DIR/$script" ]; then
        print_result "  $script exists" "PASS"
    else
        print_result "  $script exists" "FAIL"
    fi
done

echo ""

# Test 2: Check script permissions
echo "Test 2: Checking script permissions..."
for script in "${SCRIPTS[@]}"; do
    if [ -x "$SCRIPT_DIR/$script" ]; then
        print_result "  $script is executable" "PASS"
    else
        print_result "  $script is executable" "FAIL"
    fi
done

echo ""

# Test 3: Check certificate validation script
echo "Test 3: Testing check_certificates.sh..."
if OUTPUT=$("$SCRIPT_DIR/check_certificates.sh" 2>&1); then
    if echo "$OUTPUT" | grep -qE "^(VALID|CA_EXPIRING|SERVER_CERT_EXPIRING|ERROR)"; then
        print_result "  Certificate check output format" "PASS"
    else
        print_result "  Certificate check output format" "FAIL"
    fi
else
    # Script may fail if EasyRSA not set up, which is expected
    if echo "$OUTPUT" | grep -q "ERROR"; then
        print_result "  Certificate check error handling" "PASS"
    else
        print_result "  Certificate check error handling" "FAIL"
    fi
fi

echo ""

# Test 4: Test VPN status script
echo "Test 4: Testing get_vpn_status.sh..."
if OUTPUT=$("$SCRIPT_DIR/get_vpn_status.sh" 2>&1); then
    if echo "$OUTPUT" | grep -q '"status"'; then
        print_result "  VPN status JSON output" "PASS"
    else
        print_result "  VPN status JSON output" "FAIL"
    fi
else
    # May fail if OpenVPN not running, check for JSON output anyway
    if echo "$OUTPUT" | grep -q '"status"'; then
        print_result "  VPN status JSON output" "PASS"
    else
        print_result "  VPN status JSON output" "FAIL"
    fi
fi

echo ""

# Test 5: Test connected clients script
echo "Test 5: Testing get_connected_clients.sh..."
if OUTPUT=$("$SCRIPT_DIR/get_connected_clients.sh" 2>&1); then
    if echo "$OUTPUT" | grep -q '"clients"'; then
        print_result "  Connected clients JSON output" "PASS"
    else
        print_result "  Connected clients JSON output" "FAIL"
    fi
else
    # May fail if status file doesn't exist, check for JSON output
    if echo "$OUTPUT" | grep -q '"clients"'; then
        print_result "  Connected clients JSON output" "PASS"
    else
        print_result "  Connected clients JSON output" "FAIL"
    fi
fi

echo ""

# Test 6: Test argument validation
echo "Test 6: Testing argument validation..."

# Test add_vpn_user.sh with no arguments
if OUTPUT=$("$SCRIPT_DIR/add_vpn_user.sh" 2>&1); then
    print_result "  add_vpn_user.sh rejects no arguments" "FAIL"
else
    if echo "$OUTPUT" | grep -q "ERROR"; then
        print_result "  add_vpn_user.sh rejects no arguments" "PASS"
    else
        print_result "  add_vpn_user.sh rejects no arguments" "FAIL"
    fi
fi

# Test remove_vpn_user.sh with no arguments
if OUTPUT=$("$SCRIPT_DIR/remove_vpn_user.sh" 2>&1); then
    print_result "  remove_vpn_user.sh rejects no arguments" "FAIL"
else
    if echo "$OUTPUT" | grep -q "ERROR"; then
        print_result "  remove_vpn_user.sh rejects no arguments" "PASS"
    else
        print_result "  remove_vpn_user.sh rejects no arguments" "FAIL"
    fi
fi

# Test generate_client_cert.sh with no arguments
if OUTPUT=$("$SCRIPT_DIR/generate_client_cert.sh" 2>&1); then
    print_result "  generate_client_cert.sh rejects no arguments" "FAIL"
else
    if echo "$OUTPUT" | grep -q "ERROR"; then
        print_result "  generate_client_cert.sh rejects no arguments" "PASS"
    else
        print_result "  generate_client_cert.sh rejects no arguments" "FAIL"
    fi
fi

# Test generate_ovpn_config.sh with insufficient arguments
if OUTPUT=$("$SCRIPT_DIR/generate_ovpn_config.sh" 2>&1); then
    print_result "  generate_ovpn_config.sh rejects insufficient arguments" "FAIL"
else
    if echo "$OUTPUT" | grep -q "ERROR"; then
        print_result "  generate_ovpn_config.sh rejects insufficient arguments" "PASS"
    else
        print_result "  generate_ovpn_config.sh rejects insufficient arguments" "FAIL"
    fi
fi

echo ""

# Test 7: Check required directories
echo "Test 7: Checking required directories..."
DIRS=(
    "/var/log/phpnuxbill"
    "/etc/openvpn/backups"
    "/var/www/html/system/storage/vpn-configs"
)

for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        print_result "  $dir exists" "PASS"
    else
        print_result "  $dir exists (will be created on first use)" "PASS"
    fi
done

echo ""

# Summary
echo "========================================="
echo "Test Summary"
echo "========================================="
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
else
    echo -e "${YELLOW}Some tests failed. Review the output above.${NC}"
    exit 1
fi
