<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNAlertManager class
 * Manages VPN-related email alerts
 */
class VPNAlertManager
{
    /**
     * Send disconnection alert
     *
     * @param int $routerId
     * @param array $details
     * @return bool
     */
    public static function sendDisconnectionAlert($routerId, $details = [])
    {
        global $config;
        
        // Check if alerts are enabled
        if (empty($config['vpn_alert_on_disconnect'])) {
            return false;
        }

        $router = ORM::for_table('tbl_routers')->find_one($routerId);
        if (!$router) {
            return false;
        }

        $alertEmail = $config['vpn_alert_email'] ?? $config['user_email'] ?? '';
        if (empty($alertEmail)) {
            return false;
        }

        $subject = 'VPN Disconnection Alert - ' . $router['name'];
        
        $message = "VPN connection to router has been lost.\n\n";
        $message .= "Router Details:\n";
        $message .= "- Name: " . $router['name'] . "\n";
        $message .= "- VPN Username: " . $router['vpn_username'] . "\n";
        $message .= "- VPN IP: " . $router['vpn_ip'] . "\n";
        $message .= "- Last Check: " . $router['last_vpn_check'] . "\n";
        $message .= "- Status: " . $router['ovpn_status'] . "\n\n";
        
        if (!empty($details['error'])) {
            $message .= "Error Details:\n" . $details['error'] . "\n\n";
        }
        
        $message .= "Please check the router and VPN connection.\n";
        $message .= "Login to phpnuxbill to view more details.";

        try {
            Message::sendEmail($alertEmail, $subject, $message);
            return true;
        } catch (Exception $e) {
            // Log error but don't throw exception
            return false;
        }
    }

    /**
     * Send certificate expiry alert
     *
     * @param int $routerId
     * @param int $daysRemaining
     * @return bool
     */
    public static function sendCertificateExpiryAlert($routerId, $daysRemaining)
    {
        global $config;
        
        $router = ORM::for_table('tbl_routers')->find_one($routerId);
        if (!$router) {
            return false;
        }

        $cert = ORM::for_table('tbl_vpn_certificates')
            ->where('router_id', $routerId)
            ->where('status', 'active')
            ->find_one();
        
        if (!$cert) {
            return false;
        }

        $alertEmail = $config['vpn_alert_email'] ?? $config['user_email'] ?? '';
        if (empty($alertEmail)) {
            return false;
        }

        $subject = 'VPN Certificate Expiry Alert - ' . $router['name'];
        
        $message = "VPN certificate is expiring soon.\n\n";
        $message .= "Router Details:\n";
        $message .= "- Name: " . $router['name'] . "\n";
        $message .= "- VPN Username: " . $router['vpn_username'] . "\n";
        $message .= "- Certificate Expiry: " . $cert['expiry_date'] . "\n";
        $message .= "- Days Remaining: " . $daysRemaining . "\n\n";
        
        $message .= "Action Required:\n";
        $message .= "Please renew the certificate before it expires to avoid connection issues.\n\n";
        
        $renewUrl = U . 'routers/renew-certificate/' . $routerId;
        $message .= "Renew Certificate: " . $renewUrl . "\n";

        try {
            Message::sendEmail($alertEmail, $subject, $message);
            return true;
        } catch (Exception $e) {
            // Log error but don't throw exception
            return false;
        }
    }
}
