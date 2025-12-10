<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceFingerprint;

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
                'ip'        => $this->ip,
                'port'      => $this->port,
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

    /**
     * Pull all fingerprints for all users from device.
     *
     * Returns array of:
     *  [
     *    'device_uid'   => int,
     *    'user_code'    => string,
     *    'user_name'    => ?string,
     *    'finger_index' => int,
     *    'template_raw' => string (binary)
     *  ]
     */
    public function pullAllFingerprints(): array
    {
        if (! $this->connect()) {
            return [];
        }

        $results = [];

        try {
            $zk    = $this->client;
            $users = $zk->getUsers() ?? [];

            foreach ($users as $user) {
                // common fields for CodingLibs SDK
                $uid      = (int) ($user['uid'] ?? $user['UID'] ?? 0);
                $userCode = (string) ($user['userid'] ?? $user['UserID'] ?? $uid);
                $userName = $user['name'] ?? $user['Name'] ?? null;

                if (! $uid) {
                    continue;
                }

                $fpData = $zk->getFingerprint($uid);

                if (empty($fpData)) {
                    continue;
                }

                $rows = [];

                if (is_array($fpData)) {
                    foreach ($fpData as $key => $val) {
                        // Case A: value is array with fields inside
                        if (is_array($val)) {
                            $fingerIndex = null;

                            // try to detect finger index from known keys
                            foreach (['finger', 'FingerID', 'fid', 'fingerIndex'] as $idxKey) {
                                if (isset($val[$idxKey])) {
                                    $fingerIndex = (int) $val[$idxKey];
                                    break;
                                }
                            }

                            // fallback: numeric array key
                            if ($fingerIndex === null && is_numeric($key)) {
                                $fingerIndex = (int) $key;
                            }

                            // extract template raw data
                            $tplRaw = $val['template']
                                ?? $val['Template']
                                ?? $val['tpl']
                                ?? $val['data']
                                ?? null;

                            if ($tplRaw === null) {
                                foreach ($val as $v) {
                                    if (is_string($v)) {
                                        $tplRaw = $v;
                                        break;
                                    }
                                }
                            }

                            if (! is_string($tplRaw) || $tplRaw === '') {
                                continue;
                            }

                            $rows[] = [
                                'finger_index' => $fingerIndex ?? 0,
                                'template_raw' => $tplRaw,
                            ];
                        }

                        // Case B: simple [fingerIndex => binaryTemplate]
                        else {
                            if (! is_string($val) || $val === '') {
                                continue;
                            }

                            $fingerIndex = is_numeric($key) ? (int) $key : 0;

                            $rows[] = [
                                'finger_index' => $fingerIndex,
                                'template_raw' => $val,
                            ];
                        }
                    }
                } elseif (is_string($fpData) && $fpData !== '') {
                    // Single template, assume finger 0
                    $rows[] = [
                        'finger_index' => 0,
                        'template_raw' => $fpData,
                    ];
                }

                foreach ($rows as $row) {
                    $fingerIndex = (int) $row['finger_index'];
                    $tplRaw      = $row['template_raw'];

                    // Save directly to DB as base64
                    $templateBase64 = base64_encode($tplRaw);

                    DeviceFingerprint::updateOrCreate(
                        [
                            'device_ip'    => $this->ip,
                            'device_uid'   => $uid,
                            'finger_index' => $fingerIndex,
                        ],
                        [
                            'user_code'       => $userCode,
                            'user_name'       => $userName,
                            'template_base64' => $templateBase64,
                            'template_type'   => 'zk',
                        ]
                    );

                    $results[] = [
                        'device_uid'   => $uid,
                        'user_code'    => $userCode,
                        'user_name'    => $userName,
                        'finger_index' => $fingerIndex,
                        'template_raw' => $tplRaw,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error('ZkTemplatePullService pullAllFingerprints error: '.$e->getMessage(), [
                'ip'        => $this->ip,
                'port'      => $this->port,
                'exception' => $e,
            ]);
        } finally {
            $this->disconnect();
        }

        return $results;
    }
}
