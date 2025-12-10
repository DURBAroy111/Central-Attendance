<?php

namespace App\Services;

use App\Models\DeviceFingerprint;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use CodingLibs\ZktecoPhp\Libs\Util;
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
                'ip'        => $this->ip,
                'port'      => $this->port,
                'exception' => $e,
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
     * Push a single DeviceFingerprint to the zkteco device.
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

            $userId   = (string) $df->user_code;
            $userName = $df->user_name ?: $userId;
            $fingerIndex = (int) ($df->finger_index ?? 0);

            // Use accessor from DeviceFingerprint: template_binary
            $binaryTemplate = $df->template_binary; // decoded from base64 or raw

            if ($binaryTemplate === null || $binaryTemplate === '') {
                Log::warning('ZkTemplatePushService: empty template', [
                    'df_id' => $df->id,
                ]);

                return false;
            }

            // 1️⃣ Ensure user exists on the device
            try {
                /**
                 * Signature (from coding-libs/zkteco-php):
                 * setUser(int $uid, $userid, string $name, $password, int $role = Util::LEVEL_USER, int $cardno = 0): bool
                 */
                $userOk = $this->client->setUser(
                    $uid,
                    $userId,
                    $userName,
                    '',                 // password
                    Util::LEVEL_USER,   // role (integer)
                    0                   // card number
                );

                Log::info('ZkTemplatePushService: setUser result', [
                    'ip'        => $this->ip,
                    'df_id'     => $df->id,
                    'uid'       => $uid,
                    'userId'    => $userId,
                    'userName'  => $userName,
                    'userOk'    => $userOk ? 1 : 0,
                ]);
            } catch (\Throwable $e) {
                Log::warning('ZkTemplatePushService: setUser exception', [
                    'ip'      => $this->ip,
                    'df_id'   => $df->id,
                    'uid'     => $uid,
                    'message' => $e->getMessage(),
                ]);
                // we still try fingerprint, but this is a red flag
            }

            // 2️⃣ Now push fingerprint
            $payload = [
                $fingerIndex => $binaryTemplate,
            ];

            Log::info('ZkTemplatePushService: calling setFingerprint', [
                'ip'          => $this->ip,
                'df_id'       => $df->id,
                'uid'         => $uid,
                'user_code'   => $userId,
                'user_name'   => $userName,
                'fingerIndex' => $fingerIndex,
                'tpl_length'  => strlen($binaryTemplate),
                'template_type' => $df->template_type,
            ]);

            // According to the library, this normally returns bool
            $result = $this->client->setFingerprint($uid, $payload);

            Log::info('ZkTemplatePushService: setFingerprint raw result', [
                'ip'      => $this->ip,
                'df_id'   => $df->id,
                'uid'     => $uid,
                'result'  => $result,
                'as_int'  => (int) $result,
            ]);

            if ($result) {
                Log::info('ZkTemplatePushService: template pushed', [
                    'ip'          => $this->ip,
                    'df_id'       => $df->id,
                    'uid'         => $uid,
                    'fingerIndex' => $fingerIndex,
                ]);

                return true;
            }

            Log::warning('ZkTemplatePushService: setFingerprint failed / returned falsy', [
                'ip'          => $this->ip,
                'df_id'       => $df->id,
                'uid'         => $uid,
                'fingerIndex' => $fingerIndex,
                'tpl_length'  => strlen($binaryTemplate),
                'template_type' => $df->template_type,
                'device_ip'   => $df->device_ip,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('ZkTemplatePushService push exception: '.$e->getMessage(), [
                'ip'      => $this->ip,
                'df_id'   => $df->id,
                'user_id' => $df->user_code,
                'exception' => $e,
            ]);

            return false;
        } finally {
            $this->disconnect();
        }
    }
}
