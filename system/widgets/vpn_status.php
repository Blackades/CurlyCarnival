<?php

/**
 * VPN Status Widget
 * Displays statistics for remote router VPN connections
 */

class vpn_status
{
    public function getWidget($widget = null)
    {
        global $ui;

        try {
            // Count total remote routers
            $total = ORM::for_table('tbl_routers')
                ->where('connection_type', 'remote')
                ->count();

            // Count connected routers
            $connected = ORM::for_table('tbl_routers')
                ->where('connection_type', 'remote')
                ->where('ovpn_status', 'connected')
                ->count();

            // Count disconnected routers
            $disconnected = ORM::for_table('tbl_routers')
                ->where('connection_type', 'remote')
                ->where('ovpn_status', 'disconnected')
                ->count();

            // Count routers with errors
            $error = ORM::for_table('tbl_routers')
                ->where('connection_type', 'remote')
                ->where('ovpn_status', 'error')
                ->count();

            // Count pending routers
            $pending = ORM::for_table('tbl_routers')
                ->where('connection_type', 'remote')
                ->where('ovpn_status', 'pending')
                ->count();

            // Calculate connection rate percentage
            $connection_rate = 0;
            if ($total > 0) {
                $connection_rate = round(($connected / $total) * 100, 1);
            }

            // Assign variables to template
            $ui->assign('vpn_total', $total);
            $ui->assign('vpn_connected', $connected);
            $ui->assign('vpn_disconnected', $disconnected);
            $ui->assign('vpn_error', $error);
            $ui->assign('vpn_pending', $pending);
            $ui->assign('vpn_connection_rate', $connection_rate);

            // Return rendered widget
            return $ui->fetch('widget/vpn_status.tpl');
        } catch (Exception $e) {
            return '<div class="alert alert-danger">Error loading VPN status: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
