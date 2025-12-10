<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\DeviceFingerprint;
use Rats\Zkteco\Lib\ZKTeco;
use App\Services\ZkTemplatePullService;
use App\Models\Fingerprint;
use App\Jobs\PushTemplateToDevice;
use App\Services\ZkDeviceService;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::orderBy('created_at', 'desc')->get();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string',
            'ip_address'    => 'required|ip',
            'port'          => 'nullable|integer',
            'serial_number' => 'nullable|string',
        ]);

        $data['port'] = $data['port'] ?? 4370;

        // Check device online/offline
        $isOnline = Device::ping($data['ip_address'], (int) $data['port']);

        $data['status']       = $isOnline ? 'online' : 'offline';
        $data['last_seen_at'] = $isOnline ? now() : null;

        // Create device
        $device = Device::create($data);

        $pullCount  = 0;
        $pushQueued = 0;

        if ($isOnline) {
            // Pull existing templates from this device
            $pullCount = $this->syncFingerprintsFromDevice($device);

            // Queue push of missing templates from DB -> this device
            $pushQueued = $this->queuePushAllFingerprintsToDevice($device);
        }

        return redirect()
            ->route('devices.index')
            ->with(
                'success',
                'Device created and status checked as ' . ($isOnline ? 'ONLINE' : 'OFFLINE') .
                ($isOnline
                    ? ". Pulled {$pullCount} fingerprints from device and queued {$pushQueued} pushes to device."
                    : '.')
            );
    }

    public function show(Device $device)
    {
        return view('devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'name'          => 'required|string',
            'ip_address'    => 'required|ip',
            'port'          => 'nullable|integer',
            'serial_number' => 'nullable|string',
        ]);

        $data['port'] = $data['port'] ?? 4370;

        // Check device online/offline
        $isOnline = Device::ping($data['ip_address'], (int) $data['port']);

        $data['status']       = $isOnline ? 'online' : 'offline';
        $data['last_seen_at'] = $isOnline ? now() : null;

        $device->update($data);
        $device->refresh();

        $pullCount = 0;
        $pushCount = 0;

        if ($isOnline) {
            $pullCount = $this->syncFingerprintsFromDevice($device);
            $pushCount = $this->queuePushAllFingerprintsToDevice($device);
        }

        return redirect()
            ->route('devices.show', $device)
            ->with(
                'success',
                'Device updated and status checked as ' . ($isOnline ? 'ONLINE' : 'OFFLINE') .
                ($isOnline
                    ? ". Pulled {$pullCount} fingerprints from device and pushed {$pushCount} fingerprints to device."
                    : '.')
            );
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device deleted.');
    }

    public function pingNow(Device $device)
    {
        $ip   = $device->ip_address;
        $port = $device->port ?? 4370;

        try {
            $isOnline = Device::ping($ip, (int) $port);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to ping device: ' . $e->getMessage());
        }

        $device->status = $isOnline ? 'online' : 'offline';

        if ($isOnline) {
            $device->last_seen_at = now();
        }

        $device->save();

        return back()->with(
            'success',
            'Ping complete. Device is currently ' . strtoupper($device->status) . '.'
        );
    }

    /**
     * Connect via Rats\Zkteco\Lib\ZKTeco (legacy/debug)
     */
    protected function connectZk(Device $device): ?ZKTeco
    {
        $ip   = $device->ip_address;
        $port = $device->port ?? 4370;

        if (! $ip) {
            return null;
        }

        @ini_set('default_socket_timeout', '3');

        $zk = new ZKTeco($ip, $port);

        try {
            if (! $zk->connect()) {
                return null;
            }
        } catch (\Throwable $e) {
            Log::error('ZKTeco connect failed for '.$ip.': '.$e->getMessage(), [
                'exception' => $e,
            ]);
            return null;
        }

        return $zk;
    }

    /**
     * Pull all fingerprints from a device (using CodingLibs ZkTemplatePullService)
     */
    protected function syncFingerprintsFromDevice(Device $device): int
    {
        $ip   = $device->ip_address;
        $port = (int) ($device->port ?? 4370);

        if (! $ip) {
            return 0;
        }

        $service = new ZkTemplatePullService($ip, $port);
        $all     = $service->pullAllFingerprints();

        $count = 0;

        foreach ($all as $item) {
            $deviceUid   = (int) $item['device_uid'];
            $userCode    = (string) $item['user_code'];
            $userName    = $item['user_name'];
            $fingerIndex = (int) $item['finger_index'];
            $tplRaw      = $item['template_raw'];

            if (! $deviceUid || $tplRaw === null) {
                continue;
            }

            $templateBase64 = base64_encode($tplRaw);

            DeviceFingerprint::updateOrCreate(
                [
                    'device_ip'    => $ip,
                    'device_uid'   => $deviceUid,
                    'finger_index' => $fingerIndex,
                ],
                [
                    'user_code'       => $userCode,
                    'user_name'       => $userName,
                    'template_base64' => $templateBase64,
                    'template_type'   => 'zk',
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Queue push of all missing fingerprints from DB to this device.
     */
    protected function queuePushAllFingerprintsToDevice(Device $device): int
    {
        $ip = $device->ip_address;

        if (! $ip) {
            return 0;
        }

        // existing fingerprints already on THIS device (by user_code + finger_index)
        $existing = DeviceFingerprint::forDevice($ip)->get(['user_code', 'finger_index']);

        $existingMap = [];
        foreach ($existing as $df) {
            $key = (string) $df->user_code . ':' . (int) ($df->finger_index ?? 0);
            $existingMap[$key] = true;
        }

        $queued   = 0;
        $deviceId = $device->id;

        // Any template in DB can be used as "source" to push to this device
        DeviceFingerprint::whereNotNull('template_base64')
            ->orderBy('id')
            ->chunk(100, function ($rows) use (&$queued, &$existingMap, $deviceId) {
                /** @var \App\Models\DeviceFingerprint $df */
                foreach ($rows as $df) {
                    $key = (string) $df->user_code . ':' . (int) ($df->finger_index ?? 0);

                    if (isset($existingMap[$key])) {
                        continue;
                    }

                    PushTemplateToDevice::dispatch($deviceId, $df->id);
                    $queued++;

                    $existingMap[$key] = true;
                }
            });

        return $queued;
    }
}
