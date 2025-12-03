<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fingerprint;
use App\Models\Device;
use App\Models\DeviceFingerprint;
use App\Jobs\PushTemplateToDevice;


class EnrollController extends Controller
{
   public function enroll(Request $r)
{
    $data = $r->validate([
        'employee_code' => 'required',
        'name'          => 'required',
        'template'      => 'required',
        'finger_index'  => 'nullable|integer',
    ]);

    // 1) Create or get the user
    $user = User::firstOrCreate(
        ['employee_code' => $data['employee_code']],
        ['name'          => $data['name']]
    );

    // 2) Save fingerprint (encrypted)
    $fp = Fingerprint::create([
        'user_id'             => $user->id,
        'template'            => $data['template'],
        'template_type'       => 'zk',
        'finger_index'        => $data['finger_index'] ?? 0,
        'registered_device_id'=> null,
    ]);

    // 3) Compute a stable uid for the device
    $uid = (int) $user->employee_code;
    if (!$uid) {
        $uid = (int) $user->id;
    }

    // 4) For each device, sync fingerprint
    foreach (Device::all() as $device) {
        if (!$device->ip_address) {
            continue;
        }

        $df = DeviceFingerprint::updateOrCreate(
            [
                'device_ip'    => $device->ip_address,
                'device_uid'   => $uid,
                'finger_index' => $data['finger_index'] ?? 0,
            ],
            [
                'user_code'       => $user->employee_code,
                'user_name'       => $user->name,
                'template_base64' => base64_encode($fp->template),
                'template_type'   => 'zk',
            ]
        );

        PushTemplateToDevice::dispatch($device->id, $df->id);
    }

    return response()->json([
        'status'  => 'ok',
        'user_id' => $user->id,
    ]);
}

}
