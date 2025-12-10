<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;

class ShowZKBiometrics extends Command
{
    /**
     * Usage:
     *   php artisan zk:show-biometrics
     */
    protected $signature = 'zk:show-biometrics';

    protected $description = 'Read ALL fingerprint biometric templates from ZKTeco iClock 9000 (192.168.1.237) and print them';

    public function handle()
    {
        $ip   = '192.168.1.109'; // device IP
        $port = 4370;            

        $this->info("Connecting to ZKTeco iClock 9000 at {$ip}:{$port} ...");

        $zk = new ZKTeco($ip, $port);

        if (! $zk->connect()) {
            $this->error("Cannot connect to ZKTeco device at {$ip}:{$port}.");
            return Command::FAILURE;
        }

        
        try {
            $zk->disableDevice();
        } catch (\Throwable $e) {
            $this->warn('Could not disable device, continuing anyway: ' . $e->getMessage());
        }

        $this->info('Connected. Fetching users from device...');
        $users = $zk->getUser();

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

        $totalTemplates = 0;

        foreach ($users as $u) {
            $deviceUid = $u['uid'];
            $userCode  = $u['userid'];
            $userName  = $u['name'] ?? '';

            $this->line("");
            $this->line("=== USER ON DEVICE ===");
            $this->line("UID       : {$deviceUid}");
            $this->line("User code : {$userCode}");
            $this->line("Name      : {$userName}");

            // Get fingerprints for this user
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

            foreach ($fingerprints as $fingerIndex => $tplData) {
                if (is_array($tplData) && isset($tplData['tpl'])) {
                    $binaryTemplate = $tplData['tpl'];
                } else {
                    $binaryTemplate = $tplData; // raw binary string
                }

                if (! $binaryTemplate) {
                    continue;
                }

                $totalTemplates++;

                // Biometric data in text-friendly formats
                $base64 = base64_encode($binaryTemplate);     
                $hex    = bin2hex($binaryTemplate);           

                $this->line("  → Fingerprint #{$totalTemplates}");
                $this->line("    finger_index : {$fingerIndex}");
                $this->line("    template_base64 :");
                $this->line("      {$base64}");
                $this->line("    template_hex (optional):");
                $this->line("      {$hex}");
            }
        }

        // Re-enable device and disconnect
        try {
            $zk->enableDevice();
        } catch (\Throwable $e) {
            $this->warn('Could not re-enable device: ' . $e->getMessage());
        }

        $zk->disconnect();

        $this->info("");
        $this->info("Done. Total fingerprint templates read: {$totalTemplates}");

        return Command::SUCCESS;
    }
}
