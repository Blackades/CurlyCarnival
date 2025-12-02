<?php

/**
 * VPNCertificate Model - ORM model for tbl_vpn_certificates
 * Manages certificate lifecycle and expiry tracking
 */

class VPNCertificate
{
    /**
     * Check if certificate has expired
     * 
     * @param object $certificate ORM instance of certificate
     * @return bool True if certificate expiry date has passed
     */
    public static function isExpired($certificate)
    {
        if (!isset($certificate->expiry_date)) {
            return false;
        }

        $expiryDate = new DateTime($certificate->expiry_date);
        $now = new DateTime();
        
        return $now > $expiryDate;
    }

    /**
     * Check if certificate expires within specified days
     * 
     * @param object $certificate ORM instance of certificate
     * @param int $days Number of days to check (default: 30)
     * @return bool True if certificate expires within specified days
     */
    public static function isExpiringSoon($certificate, $days = 30)
    {
        if (!isset($certificate->expiry_date)) {
            return false;
        }

        $expiryDate = new DateTime($certificate->expiry_date);
        $now = new DateTime();
        $threshold = new DateTime();
        $threshold->modify("+{$days} days");
        
        return $now < $expiryDate && $expiryDate <= $threshold;
    }

    /**
     * Calculate days remaining until certificate expiry
     * 
     * @param object $certificate ORM instance of certificate
     * @return int Number of days until expiry (negative if expired)
     */
    public static function getDaysUntilExpiry($certificate)
    {
        if (!isset($certificate->expiry_date)) {
            return 0;
        }

        $expiryDate = new DateTime($certificate->expiry_date);
        $now = new DateTime();
        
        $interval = $now->diff($expiryDate);
        
        // Return negative value if expired
        return $interval->invert ? -$interval->days : $interval->days;
    }

    /**
     * Query certificates expiring within specified days
     * 
     * @param int $days Number of days to check (default: 30)
     * @return array Array of certificate records expiring soon
     */
    public static function getExpiringSoon($days = 30)
    {
        $now = new DateTime();
        $threshold = new DateTime();
        $threshold->modify("+{$days} days");
        
        $certificates = ORM::for_table('tbl_vpn_certificates')
            ->where('status', 'active')
            ->where_gte('expiry_date', $now->format('Y-m-d H:i:s'))
            ->where_lte('expiry_date', $threshold->format('Y-m-d H:i:s'))
            ->order_by_asc('expiry_date')
            ->find_many();

        return $certificates;
    }
}
