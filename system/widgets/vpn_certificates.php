<?php

/**
 * VPN Certificates Widget
 * Displays certificate expiration statistics for remote routers
 */

class vpn_certificates
{
    public function getWidget($widget = null)
    {
        global $ui;

        try {
            $today = date('Y-m-d');
            $thirtyDaysFromNow = date('Y-m-d', strtotime('+30 days'));

            // Count expired certificates
            $expired = ORM::for_table('tbl_vpn_certificates')
                ->where('status', 'active')
                ->where_lt('expiry_date', $today)
                ->count();

            // Count certificates expiring within 30 days
            $expiring_soon = ORM::for_table('tbl_vpn_certificates')
                ->where('status', 'active')
                ->where_gte('expiry_date', $today)
                ->where_lte('expiry_date', $thirtyDaysFromNow)
                ->count();

            // Count total active certificates
            $total_active = ORM::for_table('tbl_vpn_certificates')
                ->where('status', 'active')
                ->count();

            // Count revoked certificates
            $revoked = ORM::for_table('tbl_vpn_certificates')
                ->where('status', 'revoked')
                ->count();

            // Get list of certificates expiring within 30 days with router details
            $expiring_certs = ORM::for_table('tbl_vpn_certificates')
                ->select('tbl_vpn_certificates.*')
                ->select('tbl_routers.name', 'router_name')
                ->select('tbl_routers.id', 'router_id')
                ->join('tbl_routers', array('tbl_vpn_certificates.router_id', '=', 'tbl_routers.id'))
                ->where('tbl_vpn_certificates.status', 'active')
                ->where_gte('tbl_vpn_certificates.expiry_date', $today)
                ->where_lte('tbl_vpn_certificates.expiry_date', $thirtyDaysFromNow)
                ->order_by_asc('tbl_vpn_certificates.expiry_date')
                ->limit(5)
                ->find_array();

            // Calculate days until expiry for each certificate
            foreach ($expiring_certs as &$cert) {
                $expiry = new DateTime($cert['expiry_date']);
                $now = new DateTime($today);
                $diff = $now->diff($expiry);
                $cert['days_remaining'] = $diff->days;
            }

            // Assign variables to template
            $ui->assign('cert_expired', $expired);
            $ui->assign('cert_expiring_soon', $expiring_soon);
            $ui->assign('cert_total_active', $total_active);
            $ui->assign('cert_revoked', $revoked);
            $ui->assign('expiring_certs', $expiring_certs);

            // Return rendered widget
            return $ui->fetch('widget/vpn_certificates.tpl');
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error loading certificate status: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
