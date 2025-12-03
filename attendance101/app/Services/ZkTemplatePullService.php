<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Log;

class ZkTemplatePullService
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
     * Connect to device (using coding-libs/zkteco-php)
     */
    protected function connect(): bool
    {
        try {
            $this->client = new ZKTeco(
                ip: $this->ip,
                port: $this->port,
                shouldPing: false,
                timeout: 10
            );

            $this->client->connect();
            return true;
        } catch (\Throwable $e) {
            Log::error('ZkTemplatePullService connect failed: '.$e->getMessage(), [
                'ip' => $this->ip,
                'port' => $this->port,
                'exception' => $e,
            ]);
            $this->client = null;
            return false;
        }
    }

    /**
     * Disconnect safely.
     */
    protected function disconnect(): void
    {
        if (! $this->client) {
            return;
        }

        try {
            $this->client->disconnect();
        } catch (\Throwable $e) {
            // ignore
        }

        $this->client = null;
    }

    
    public function pullAllFingerprints(): array
    {
        if (! $this->connect()) {
            return [];
        }

        $results = [];

        try {
            $zk = $this->client;

            // 1) Get all users
            $users = $zk->getUsers() ?? [];

            foreach ($users as $user) {
                // You may need to adjust these key names after a dd($users).
                $uid       = (int) ($user['uid'] ?? $user['UID'] ?? 0);
                $userCode  = (string) ($user['userid'] ?? $user['UserID'] ?? $uid);
                $userName  = $user['name'] ?? $user['Name'] ?? null;

                if (! $uid) {
                    continue;
                }

                // 2) Get fingerprint(s) for this user
                //    The exact shape of this result depends on the library/device.
                $fpData = $zk->getFingerprint($uid);

                // Case 1: multiple fingerprints as array of arrays
                if (is_array($fpData) && isset($fpData[0]) && is_array($fpData[0])) {
                    foreach ($fpData as $fp) {
                        $fingerIndex = (int) ($fp['finger'] ?? $fp['FingerID'] ?? 0);

                        // pick the template field or fallback to first scalar value
                        $tplRaw = $fp['template']
                            ?? $fp['Template']
                            ?? $fp['data']
                            ?? null;

                        if ($tplRaw === null) {
                            // try grab first scalar in the array
                            foreach ($fp as $v) {
                                if (is_string($v)) {
                                    $tplRaw = $v;
                                    break;
                                }
                            }
                        }

                        if ($tplRaw === null) {
                            continue;
                        }

                        $results[] = [
                            'device_uid'   => $uid,
                            'user_code'    => $userCode,
                            'user_name'    => $userName,
                            'finger_index' => $fingerIndex,
                            'template_raw' => $tplRaw,
                        ];
                    }
                }
                // Case 2: single fingerprint (string or simple array)
                else {
                    $fingerIndex = 0;

                    if (is_array($fpData)) {
                        $fingerIndex = (int) ($fpData['finger'] ?? $fpData['FingerID'] ?? 0);
                        $tplRaw      = $fpData['template']
                            ?? $fpData['Template']
                            ?? $fpData['data']
                            ?? null;

                        if ($tplRaw === null) {
                            foreach ($fpData as $v) {
                                if (is_string($v)) {
                                    $tplRaw = $v;
                                    break;
                                }
                            }
                        }
                    } else {
                        $tplRaw = $fpData; // assume string/binary
                    }

                    if ($tplRaw !== null) {
                        $results[] = [
                            'device_uid'   => $uid,
                            'user_code'    => $userCode,
                            'user_name'    => $userName,
                            'finger_index' => $fingerIndex,
                            'template_raw' => $tplRaw,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('ZkTemplatePullService pullAllFingerprints error: '.$e->getMessage(), [
                'ip' => $this->ip,
                'port' => $this->port,
                'exception' => $e,
            ]);
        } finally {
            $this->disconnect();
        }

        return $results;
    }
}
