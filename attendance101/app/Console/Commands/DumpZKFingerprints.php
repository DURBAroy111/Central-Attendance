<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;

class DumpZKFingerprints extends Command
{
    /**
     * Usage:
     *   php artisan zk:dump-fingerprints
     */
    protected $signature = 'zk:dump-fingerprints';

    protected $description = 'Load ALL fingerprint templates from ZKTeco iClock 9000 (192.168.1.237) and dump them to a JSON file';

    public function handle()
    {
        $ip   = '192.168.1.237'; 
        $port = 4370;            

        $this->info("Connecting to ZKTeco iClock 9000 at {$ip}:{$port} ...");

        $zk = new ZKTeco($ip, $port);

        if (! $zk->connect()) {
            $this->error("❌ Cannot connect to ZKTeco device at {$ip}:{$port}.");
            return Command::FAILURE;
        }

    
        try {
            $zk->disableDevice();
        } catch (\Throwable $e) {
            $this->warn('Could not disable device, continuing anyway: ' . $e->getMessage());
        }

        $this->info('Connected. Fetching users from device...');
        $users = $zk->getUser();   // array of device users

        if (empty($users)) {
            $this->warn('No users found on device.');
            try {
                $zk->enableDevice();
            } catch (\Throwable $e) {
                // ignore
            }
            $zk->disconnect();
            return Command::SUCCESS;
        }

        $this->info('Total users on device: ' . count($users));

        $allFingerprints = [];
        $totalTemplates  = 0;

        foreach ($users as $u) {
            /**
             * Typical structure from rats/zkteco:
             * $u['uid']    => internal numeric ID on device
             * $u['userid'] => string user code
             * $u['name']   => name
             */
            $deviceUid = $u['uid'];
            $userCode  = $u['userid'];
            $userName  = $u['name'] ?? '';

            $this->line("");
            $this->line("=== User on device: UID={$deviceUid}, code={$userCode}, name={$userName} ===");

            // Get all fingerprints for this device user
            try {
                $fingerprints = $zk->getFingerprint($deviceUid);
            } catch (\Throwable $e) {
                $this->error("  → Error getting fingerprints for UID={$deviceUid}: " . $e->getMessage());
                continue;
            }

            if (empty($fingerprints)) {
                $this->line("  → No fingerprints found for this user.");
                continue;
            }

            $userFpData = [
                'uid'          => $deviceUid,
                'userid'       => $userCode,
                'name'         => $userName,
                'fingerprints' => [],
            ];

            foreach ($fingerprints as $fingerIndex => $tplData) {
                if (is_array($tplData) && isset($tplData['tpl'])) {
                    $binaryTemplate = $tplData['tpl'];
                } else {
                    $binaryTemplate = $tplData; // assume raw binary string
                }

                if (! $binaryTemplate) {
                    continue;
                }

                // Keep as base64 string so JSON-safe
                $encoded = base64_encode($binaryTemplate);

                $userFpData['fingerprints'][] = [
                    'finger_index' => $fingerIndex,
                    'template'     => $encoded,
                ];

                $this->line("  → Loaded fingerprint (finger_index={$fingerIndex})");
                $totalTemplates++;
            }

            $allFingerprints[] = $userFpData;
        }

        // Re-enable device and disconnect
        try {
            $zk->enableDevice();
        } catch (\Throwable $e) {
            $this->warn('Could not re-enable device: ' . $e->getMessage());
        }

        $zk->disconnect();

        // Dump to JSON file
        $path = storage_path('app/zk_fingerprints.json');
        file_put_contents($path, json_encode($allFingerprints, JSON_PRETTY_PRINT));

        $this->info('');
        $this->info("✅ Finished. Total fingerprint templates loaded: {$totalTemplates}");
        $this->info("📁 Saved to: {$path}");

        return Command::SUCCESS;
    }
}
