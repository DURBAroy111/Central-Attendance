<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;
use App\Models\Device;
use App\Models\Fingerprint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SyncZKFingerprints extends Command
{
    protected $signature = 'zk:sync-fingerprints {device_id}';
    protected $description = 'Pull fingerprint templates from a zkteco device and store in local DB';

    public function handle()
    {
        $device = Device::findOrFail($this->argument('device_id'));

        $this->info("Connecting to zkteco device {$device->name} ({$device->ip_address}:{$device->port})");

        $zk = new ZKTeco($device->ip_address, $device->port ?: 4370);

        if (! $zk->connect()) {
            $this->error('Cannot connect to zkteco device.');
            return Command::FAILURE;
        }

        $this->info('Connected. Fetching users from device...');
        $users = $zk->getUser();   // returns array of device users

        $this->info('Total users on device: ' . count($users));

        $count = 0;

        DB::beginTransaction();

        try {
            foreach ($users as $u) {
                /**
                 * Typical structure from rats/zkteco for each user:
                 * $u['uid']  => internal numeric ID on device
                 * $u['userid'] => string user code
                 * $u['name'] => name
                 */

                $deviceUid = $u['uid'];
                $userCode  = $u['userid'];

                // Map device user to your local users table however you’re doing it.
                // Example: assume your users table has "device_user_id" OR "employee_code".
                $user = User::where('device_user_id', $deviceUid)
                            ->orWhere('employee_code', $userCode)
                            ->first();

                if (! $user) {
                    $this->warn("No local User mapped for device UID={$deviceUid}, code={$userCode}. Skipping.");
                    continue;
                }

                $this->line("  → Getting fingerprints for user #{$user->id} ({$userCode})");

                $fingerprints = $zk->getFingerprint($deviceUid);

                // The helper returns an array keyed by finger index (0–9).
                // Each item may be:
                //  - raw binary string, OR
                //  - array with ['tpl' => binaryTemplate, 'size' => N]
                if (empty($fingerprints)) {
                    $this->line("    No fingerprints found.");
                    continue;
                }

                foreach ($fingerprints as $fingerIndex => $tplData) {
                    if (is_array($tplData) && isset($tplData['tpl'])) {
                        $binaryTemplate = $tplData['tpl'];
                    } else {
                        $binaryTemplate = $tplData; // assume raw binary
                    }

                    // Store as base64 to be safe with text column + encryption
                    $encoded = base64_encode($binaryTemplate);

                    Fingerprint::updateOrCreate(
                        [
                            'user_id'             => $user->id,
                            'finger_index'        => $fingerIndex,
                            'registered_device_id'=> $device->id,
                        ],
                        [
                            'template'            => $encoded,
                            'template_type'       => 'zk',
                        ]
                    );

                    $count++;
                }
            }

            DB::commit();
            $zk->disconnect();

            $this->info("Done. Synced {$count} fingerprint templates into local DB.");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $zk->disconnect();
            $this->error('Error syncing fingerprints: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
