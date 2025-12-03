<?php

/**
 *  PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *  by https://t.me/ibnux
 **/

/**
 * CertificateManager class
 * Manages OpenVPN certificates using EasyRSA
 */
class CertificateManager
{
    private $easyrsa_dir;
    private $output_dir;
    private $ca_validity_warning_days;
    private $script_dir;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config = null)
    {
        global $config;
        
        $this->easyrsa_dir = $config['easyrsa_dir'] ?? '/etc/openvpn/easy-rsa';
        $this->output_dir = $config['vpn_storage_dir'] ?? '/var/www/html/system/storage/vpn-configs';
        $this->ca_validity_warning_days = $config['vpn_cert_warning_days'] ?? 30;
        $this->script_dir = $config['vpn_script_dir'] ?? '/var/www/html/system/scripts/vpn';
    }

    /**
     * Check certificate validity
     * Executes check_certificates.sh script
     *
     * @return array
     * @throws VPNException
     */
    public function checkCertificateValidity()
    {
        $scriptPath = $this->script_dir . '/check_certificates.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Certificate check script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        $result = $this->executeScript($scriptPath, []);
        
        // Parse output: STATUS:days or ERROR:message
        $output = trim($result['output']);
        $exitCode = $result['exit_code'];
        
        if ($exitCode === 0) {
            // Valid certificates
            return [
                'status' => 'valid',
                'message' => $output
            ];
        } elseif ($exitCode === 1) {
            throw new VPNException(
                'CA certificate is missing',
                VPNException::ERR_CA_MISSING
            );
        } elseif ($exitCode === 2) {
            // CA expiring soon
            preg_match('/CA_EXPIRING:(\d+)/', $output, $matches);
            $days = $matches[1] ?? 0;
            return [
                'status' => 'ca_expiring',
                'days_remaining' => $days,
                'message' => "CA certificate expires in {$days} days"
            ];
        } elseif ($exitCode === 3) {
            throw new VPNException(
                'Server certificate is missing',
                VPNException::ERR_SERVER_CERT_MISSING
            );
        } elseif ($exitCode === 4) {
            // Server cert expiring soon
            preg_match('/SERVER_CERT_EXPIRING:(\d+)/', $output, $matches);
            $days = $matches[1] ?? 0;
            return [
                'status' => 'server_cert_expiring',
                'days_remaining' => $days,
                'message' => "Server certificate expires in {$days} days"
            ];
        } else {
            throw new VPNException(
                'Certificate check failed: ' . $output,
                VPNException::ERR_CERT_GENERATION
            );
        }
    }

    /**
     * Generate client certificate
     *
     * @param string $clientName
     * @param int $validityDays
     * @return string Certificate path
     * @throws VPNException
     */
    public function generateClientCertificate($clientName, $validityDays = 365)
    {
        $scriptPath = $this->script_dir . '/generate_client_cert.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Certificate generation script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        $result = $this->executeScript($scriptPath, [$clientName, $validityDays]);
        
        if ($result['exit_code'] !== 0) {
            throw new VPNException(
                'Certificate generation failed: ' . $result['output'],
                VPNException::ERR_CERT_GENERATION,
                ['client' => $clientName]
            );
        }

        // Parse output: SUCCESS:output_path
        $output = trim($result['output']);
        if (preg_match('/SUCCESS:(.+)/', $output, $matches)) {
            return $matches[1];
        }

        throw new VPNException(
            'Failed to parse certificate generation output',
            VPNException::ERR_CERT_GENERATION
        );
    }

    /**
     * Revoke certificate
     *
     * @param string $clientName
     * @return bool
     * @throws VPNException
     */
    public function revokeCertificate($clientName)
    {
        $scriptPath = $this->script_dir . '/revoke_client_cert.sh';
        
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Certificate revocation script not found',
                VPNException::ERR_SCRIPT_NOT_FOUND,
                ['script' => $scriptPath]
            );
        }

        $result = $this->executeScript($scriptPath, [$clientName]);
        
        if ($result['exit_code'] !== 0) {
            throw new VPNException(
                'Certificate revocation failed: ' . $result['output'],
                VPNException::ERR_CERT_REVOCATION,
                ['client' => $clientName]
            );
        }

        return true;
    }

    /**
     * Get certificate expiry date
     *
     * @param string $certPath
     * @return DateTime
     * @throws VPNException
     */
    public function getCertificateExpiryDate($certPath)
    {
        if (!file_exists($certPath)) {
            throw new VPNException(
                'Certificate file not found',
                VPNException::ERR_FILE_NOT_FOUND,
                ['path' => $certPath]
            );
        }

        $command = "openssl x509 -enddate -noout -in " . escapeshellarg($certPath);
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new VPNException(
                'Failed to read certificate expiry date',
                VPNException::ERR_CERT_GENERATION
            );
        }

        // Parse: notAfter=Jan 1 00:00:00 2025 GMT
        $line = $output[0] ?? '';
        if (preg_match('/notAfter=(.+)/', $line, $matches)) {
            return new DateTime($matches[1]);
        }

        throw new VPNException(
            'Failed to parse certificate expiry date',
            VPNException::ERR_CERT_GENERATION
        );
    }

    /**
     * Get certificates expiring soon
     *
     * @param int $days
     * @return array
     */
    public function getCertificatesExpiringSoon($days = 30)
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $certs = ORM::for_table('tbl_vpn_certificates')
            ->where_lte('expiry_date', $expiryDate)
            ->where('status', 'active')
            ->find_many();
        
        return $certs;
    }

    /**
     * Cleanup expired certificates
     *
     * @return int Number of certificates cleaned up
     */
    public function cleanupExpiredCertificates()
    {
        $scriptPath = $this->script_dir . '/cleanup_expired_certs.sh';
        
        if (!file_exists($scriptPath)) {
            return 0;
        }

        $result = $this->executeScript($scriptPath, []);
        
        // Update database status for expired certificates
        $count = ORM::for_table('tbl_vpn_certificates')
            ->where_lt('expiry_date', date('Y-m-d'))
            ->where('status', 'active')
            ->find_result_set()
            ->set('status', 'expired')
            ->save();
        
        return $count;
    }

    /**
     * Execute script with proper error handling
     *
     * @param string $scriptPath
     * @param array $args
     * @return array
     * @throws VPNException
     */
    private function executeScript($scriptPath, $args = [])
    {
        if (!file_exists($scriptPath)) {
            throw new VPNException(
                'Script not found: ' . $scriptPath,
                VPNException::ERR_SCRIPT_NOT_FOUND
            );
        }

        // Build command with escaped arguments
        // Use 'sudo bash' to avoid secure_path issues
        $command = 'sudo bash ' . escapeshellarg($scriptPath);
        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }

        // Execute and capture output
        exec($command . ' 2>&1', $output, $exitCode);
        
        return [
            'output' => implode("\n", $output),
            'exit_code' => $exitCode
        ];
    }
}
