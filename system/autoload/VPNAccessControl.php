<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNAccessControl class
 * Manages access control for VPN-related operations
 */
class VPNAccessControl
{
    /**
     * Check if admin can manage VPN
     * Only SuperAdmin and Admin can manage VPN
     *
     * @param array $admin
     * @return bool
     */
    public static function canManageVPN($admin)
    {
        if (!is_array($admin) || !isset($admin['user_type'])) {
            return false;
        }
        
        return in_array($admin['user_type'], ['SuperAdmin', 'Admin']);
    }

    /**
     * Check if admin can view VPN logs
     * SuperAdmin, Admin, and Report can view logs
     *
     * @param array $admin
     * @return bool
     */
    public static function canViewVPNLogs($admin)
    {
        if (!is_array($admin) || !isset($admin['user_type'])) {
            return false;
        }
        
        return in_array($admin['user_type'], ['SuperAdmin', 'Admin', 'Report']);
    }

    /**
     * Check if admin can renew certificates
     * Only SuperAdmin can renew certificates
     *
     * @param array $admin
     * @return bool
     */
    public static function canRenewCertificates($admin)
    {
        if (!is_array($admin) || !isset($admin['user_type'])) {
            return false;
        }
        
        return $admin['user_type'] === 'SuperAdmin';
    }
}
