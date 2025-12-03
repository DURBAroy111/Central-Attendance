<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\DeviceFingerprint;
use App\Services\ZkTemplatePushService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PushTemplateToDevice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $deviceId;
    public int $deviceFingerprintId;

    /**
     * @param int 
     * @param int 
     */
    public function __construct(int $deviceId, int $deviceFingerprintId)
    {
        $this->deviceId            = $deviceId;
        $this->deviceFingerprintId = $deviceFingerprintId;
    }

    
    public function handle(): void
    {
        $device = Device::find($this->deviceId);
        $df     = DeviceFingerprint::find($this->deviceFingerprintId);

        if (! $device || ! $df) {
            return;
        }

        $ip   = $device->ip_address;
        $port = (int) ($device->port ?? 4370);

        if (! $ip) {
            return;
        }

        Log::info('PushTemplateToDevice starting', [
            'device_id'    => $device->id,
            'device_ip'    => $ip,
            'df_id'        => $df->id,
            'user_code'    => $df->user_code,
            'device_uid'   => $df->device_uid,
            'finger_index' => $df->finger_index,
        ]);

        $svc = new ZkTemplatePushService($ip, $port);

        $ok = $svc->push($df);

        if (! $ok) {
            
            if ($this->attempts() < 2) {
                Log::warning('PushTemplateToDevice failed, will retry', [
                    'device_ip' => $ip,
                    'df_id'     => $df->id,
                    'attempts'  => $this->attempts(),
                ]);

                $this->release(60); 
            } else {
                Log::error('PushTemplateToDevice failed after retries', [
                    'device_ip' => $ip,
                    'df_id'     => $df->id,
                ]);
            }
        } else {
            Log::info('PushTemplateToDevice success', [
                'device_ip' => $ip,
                'df_id'     => $df->id,
            ]);
        }
    }
}
