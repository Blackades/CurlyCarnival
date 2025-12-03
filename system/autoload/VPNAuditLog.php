<?php

/**
 * VPNAuditLog Model - ORM model for tbl_vpn_audit_log
 * Manages audit trail for all VPN-related administrative actions
 */

class VPNAuditLog
{
    /**
     * Insert audit log record for VPN-related actions
     * 
     * @param int $routerId Router ID
     * @param string $action Action performed (e.g., 'vpn_user_created', 'certificate_renewed')
     * @param int $adminId Admin user ID who performed the action
     * @param array|string $details Action details (will be JSON encoded if array)
     * @param string $status Action status ('success', 'failed', 'pending')
     * @param string|null $errorMessage Error message if action failed
     * @return object Created audit log record
     */
    public static function logAction($routerId, $action, $adminId, $details = [], $status = 'success', $errorMessage = null)
    {
        // Skip logging if router_id is 0 (router not yet created)
        // This prevents foreign key constraint violations during error handling
        if ($routerId == 0) {
            // Log to file instead for debugging
            error_log("VPN Audit (no router): Action=$action, Admin=$adminId, Status=$status, Details=" . 
                (is_array($details) ? json_encode($details) : $details));
            return null;
        }
        
        $log = ORM::for_table('tbl_vpn_audit_log')->create();
        $log->router_id = $routerId;
        $log->action = $action;
        $log->admin_id = $adminId;
        
        // Convert details array to JSON string
        if (is_array($details)) {
            $log->details = json_encode($details);
        } else {
            $log->details = $details;
        }
        
        // Capture admin's IP address
        $log->ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        
        $log->status = $status;
        $log->error_message = $errorMessage;
        
        $log->save();
        
        return $log;
    }

    /**
     * Retrieve audit logs for a specific router with limit
     * 
     * @param int $routerId Router ID
     * @param int $limit Maximum number of logs to retrieve (default: 50)
     * @return array Array of audit log records
     */
    public static function getRouterLogs($routerId, $limit = 50)
    {
        $logs = ORM::for_table('tbl_vpn_audit_log')
            ->where('router_id', $routerId)
            ->order_by_desc('created_at')
            ->limit($limit)
            ->find_many();

        return $logs;
    }
}
