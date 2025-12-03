<?php

namespace App\Services;

use Rats\Zkteco\Lib\ZKTeco;

class ZkDeviceService
{
    protected string $host;
    protected int $port;

    /**
     * @param string 
     * @param int    
     */
    public function __construct(string $host, int $port = 4370)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Simple connectivity check.
     * Uses the ZKTeco SDK to test if it can connect.
     */
    public function ping(): bool
    {
        $zk = new ZKTeco($this->host, $this->port);

        try {
            if (! $zk->connect()) {
                return false;
            }
            $zk->disconnect();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Push ONE fingerprint template to the device.
     *
     * @param string 
     * @param int   
     * @param int    
     * @return bool
     */
    public function pushTemplate(string $templateBase64, int $deviceUserId, int $fingerIndex): bool
    {
        $zk = new ZKTeco($this->host, $this->port);

        try {
            if (! $zk->connect()) {
                return false;
            }

            $binary = base64_decode($templateBase64, true);
            if ($binary === false) {
                $binary = $templateBase64;
            }

            $result = $zk->setFingerprint($deviceUserId, [
                $fingerIndex => $binary,
            ]);

            $zk->disconnect();

            return $result > 0;
        } catch (\Throwable $e) {
            try { $zk->disconnect(); } catch (\Throwable $ignore) {}
            return false;
        }
    }
}
