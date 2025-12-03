<?php

namespace App\Services;

use App\Models\DeviceFingerprint;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Log;

class ZkTemplatePushService
{
    protected string $ip;
    protected int $port;
    protected ?ZKTeco $client = null;

    public function __construct(string $ip, int $port = 4370)
    {
        $this->ip   = $ip;
        $this->port = $port;
    }

    /**
     * Connect to the device using the SAME SDK as ZkTemplatePullService
     */
    protected function connect(): bool
    {
        try {
            $this->client = new ZKTeco(
                ip: $this->ip,
                port: $this->port,
                shouldPing: false,
                timeout: 10,
            );

            $this->client->connect();

            return true;
        } catch (\Throwable $e) {
            Log::warning('ZkTemplatePushService connect failed: '.$e->getMessage(), [
                'ip'   => $this->ip,
                'port' => $this->port,
            ]);

            $this->client = null;

            return false;
        }
    }

    protected function disconnect(): void
    {
        if ($this->client) {
            try {
                $this->client->disconnect();
            } catch (\Throwable $e) {
                // ignore
            }

            $this->client = null;
        }
    }

    /**
     * Push a single DeviceFingerprint to the device.
     */
    public function push(DeviceFingerprint $df): bool
    {
        if (! $this->connect()) {
            // connection failed
            return false;
        }

        try {
            // Decide UID used on device
            $uid = (int) ($df->device_uid ?: $df->user_code);
            if (! $uid) {
                Log::warning('ZkTemplatePushService: missing UID for fingerprint', [
                    'df_id'     => $df->id,
                    'user_code' => $df->user_code,
                ]);

                return false;
            }

            $fingerIndex = (int) ($df->finger_index ?? 0);

            // Use accessor from DeviceFingerprint: template_binary
            $binaryTemplate = $df->template_binary; // decoded from base64 or raw

            if ($binaryTemplate === null || $binaryTemplate === '') {
                Log::warning('ZkTemplatePushService: empty template', [
                    'df_id' => $df->id,
                ]);

                return false;
            }

            // ZKTecoPhp expects [fingerIndex => binaryTemplate]
            $payload = [
                $fingerIndex => $binaryTemplate,
            ];

            $count = $this->client->setFingerprint($uid, $payload);

            if ($count > 0) {
                Log::info('ZkTemplatePushService: template pushed', [
                    'ip'          => $this->ip,
                    'df_id'       => $df->id,
                    'uid'         => $uid,
                    'fingerIndex' => $fingerIndex,
                ]);

                return true;
            }

            Log::warning('ZkTemplatePushService: setFingerprint returned 0', [
                'ip'          => $this->ip,
                'df_id'       => $df->id,
                'uid'         => $uid,
                'fingerIndex' => $fingerIndex,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('ZkTemplatePushService push exception: '.$e->getMessage(), [
                'ip'      => $this->ip,
                'df_id'   => $df->id,
                'user_id' => $df->user_code,
            ]);

            return false;
        } finally {
            $this->disconnect();
        }
    }
}
