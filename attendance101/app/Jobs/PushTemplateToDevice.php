<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable; // <- add this
use App\Models\Device;
use App\Services\ZkDeviceService;

class PushTemplateToDevice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deviceId;
    public $fingerprintId;

    public function __construct($deviceId, $fingerprintId)
    {
        $this->deviceId = $deviceId;
        $this->fingerprintId = $fingerprintId;
    }

    public function handle()
    {
        $device = Device::find($this->deviceId);
        $fp = \App\Models\Fingerprint::find($this->fingerprintId);
        if (!$device || !$fp) return;

        $svc = new ZkDeviceService($device->ip_address, $device->port);
        if (!$svc->ping()) {
            // requeue later
            $this->release(30);
            return;
        }

        $ok = $svc->pushTemplate($fp->template, $fp->user_id, $fp->finger_index ?? 0);
        if (!$ok) {
            $this->release(60);
        }
    }
}
