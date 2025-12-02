<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * VPNRollbackManager class
 * Manages rollback operations for failed VPN configurations
 */
class VPNRollbackManager
{
    private $rollbackStack = [];
    private $openVPNService;
    private $certificateManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->openVPNService = new OpenVPNService();
        $this->certificateManager = new CertificateManager();
    }

    /**
     * Add rollback action to stack
     *
     * @param string $action Action type
     * @param array $params Parameters for rollback
     * @return void
     */
    public function addRollbackAction($action, $params)
    {
        $this->rollbackStack[] = [
            'action' => $action,
            'params' => $params,
            'timestamp' => time()
        ];
    }

    /**
     * Execute all rollback actions in reverse order
     *
     * @return array Results of rollback operations
     */
    public function executeRollback()
    {
        $results = [];
        
        // Execute in reverse order (LIFO)
        while (!empty($this->rollbackStack)) {
            $item = array_pop($this->rollbackStack);
            
            try {
                switch ($item['action']) {
                    case 'removeVPNUser':
                        $this->rollbackVPNUser($item['params']);
                        $results[] = ['action' => 'removeVPNUser', 'status' => 'success'];
                        break;
                        
                    case 'revokeCertificate':
                        $this->rollbackCertificate($item['params']);
                        $results[] = ['action' => 'revokeCertificate', 'status' => 'success'];
                        break;
                        
                    case 'restoreAuthScript':
                        $this->rollbackAuthScript($item['params']);
                        $results[] = ['action' => 'restoreAuthScript', 'status' => 'success'];
                        break;
                        
                    case 'deleteRouter':
                        $this->rollbackDatabaseEntry($item['params']);
                        $results[] = ['action' => 'deleteRouter', 'status' => 'success'];
                        break;
                        
                    case 'deleteFiles':
                        $this->rollbackFiles($item['params']);
                        $results[] = ['action' => 'deleteFiles', 'status' => 'success'];
                        break;
                        
                    case 'deleteCertificateRecord':
                        $this->rollbackCertificateRecord($item['params']);
                        $results[] = ['action' => 'deleteCertificateRecord', 'status' => 'success'];
                        break;
                        
                    default:
                        $results[] = [
                            'action' => $item['action'], 
                            'status' => 'unknown',
                            'message' => 'Unknown rollback action'
                        ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'action' => $item['action'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Clear rollback stack
     *
     * @return void
     */
    public function clearRollback()
    {
        $this->rollbackStack = [];
    }

    /**
     * Rollback VPN user creation
     *
     * @param array $params ['username' => string]
     * @return void
     */
    private function rollbackVPNUser($params)
    {
        if (empty($params['username'])) {
            return;
        }

        try {
            $this->openVPNService->removeVPNUser($params['username']);
        } catch (VPNException $e) {
            // User might not exist, ignore error
        }
    }

    /**
     * Rollback certificate generation
     *
     * @param array $params ['clientName' => string]
     * @return void
     */
    private function rollbackCertificate($params)
    {
        if (empty($params['clientName'])) {
            return;
        }

        try {
            $this->certificateManager->revokeCertificate($params['clientName']);
        } catch (VPNException $e) {
            // Certificate might not exist, ignore error
        }
    }

    /**
     * Rollback authentication script changes
     *
     * @param array $params ['backupPath' => string]
     * @return void
     */
    private function rollbackAuthScript($params)
    {
        if (empty($params['backupPath']) || !file_exists($params['backupPath'])) {
            return;
        }

        $authScriptPath = '/etc/openvpn/check-auth.sh';
        $command = 'sudo cp ' . escapeshellarg($params['backupPath']) . ' ' . 
                   escapeshellarg($authScriptPath) . ' 2>&1';
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new VPNException(
                'Failed to restore authentication script',
                VPNException::ERR_AUTH_SCRIPT_RESTORE
            );
        }
    }

    /**
     * Rollback database entry
     *
     * @param array $params ['routerId' => int]
     * @return void
     */
    private function rollbackDatabaseEntry($params)
    {
        if (empty($params['routerId'])) {
            return;
        }

        $router = ORM::for_table('tbl_routers')->find_one($params['routerId']);
        if ($router) {
            $router->delete();
        }
    }

    /**
     * Rollback certificate database record
     *
     * @param array $params ['certificateId' => int]
     * @return void
     */
    private function rollbackCertificateRecord($params)
    {
        if (empty($params['certificateId'])) {
            return;
        }

        $cert = ORM::for_table('tbl_vpn_certificates')->find_one($params['certificateId']);
        if ($cert) {
            $cert->delete();
        }
    }

    /**
     * Rollback file creation
     *
     * @param array $params ['paths' => array]
     * @return void
     */
    private function rollbackFiles($params)
    {
        if (empty($params['paths']) || !is_array($params['paths'])) {
            return;
        }

        foreach ($params['paths'] as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Get current rollback stack size
     *
     * @return int
     */
    public function getStackSize()
    {
        return count($this->rollbackStack);
    }
}
