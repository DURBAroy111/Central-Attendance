<?php

namespace App\Services;

class ZkDeviceService
{
    protected $host;
    protected $port;

    public function __construct($host, $port = 4370)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function pushTemplate(string $templateBase64, int $deviceUserId, int $fingerIndex)
    {
        $payload = json_encode(['cmd' => 'push_template', 'user_id' => $deviceUserId, 'finger' => $fingerIndex, 'template' => $templateBase64]);
        $sock = @stream_socket_client("tcp://{$this->host}:{$this->port}", $errno, $errstr, 2);
        if (!$sock) {
            return false;
        }
        fwrite($sock, $payload);
        stream_set_timeout($sock, 3);
        $resp = fread($sock, 4096);
        fclose($sock);
        return $resp !== false;
    }

    public function ping(): bool
    {
        $sock = @fsockopen($this->host, $this->port, $errno, $errstr, 1);
        if ($sock) { fclose($sock); return true; }
        return false;
    }
}
