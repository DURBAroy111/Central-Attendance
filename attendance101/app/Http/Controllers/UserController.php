<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fingerprint;
use App\Models\Device;
use App\Models\DeviceFingerprint;
use App\Jobs\PushTemplateToDevice;


class UserController extends Controller
{
    public function index(){
        $users = User::with('fingerprints')->orderBy('created_at','desc')->get();
        return view('users.index', compact('users'));
    }

    public function create(){ return view('users.create'); }

    public function store(Request $r)
{
    $data = $r->validate([
        'employee_code' => 'required',
        'name'          => 'required',
        'template'      => 'nullable',
        'finger_index'  => 'nullable|integer',
    ]);

    // 1) Create the user
    $user = User::create([
        'employee_code' => $data['employee_code'],
        'name'          => $data['name'],
    ]);

    // 2) If a fingerprint template was provided, save and push it
    if (!empty($data['template'])) {

        // Save in main fingerprints table (encrypted)
        $fp = Fingerprint::create([
            'user_id'             => $user->id,
            'template'            => $data['template'],
            'template_type'       => 'zk',
            'finger_index'        => $data['finger_index'] ?? 0,
            'registered_device_id'=> null,
        ]);

        // uid on device: try employee_code (if numeric), otherwise fallback to user id
        $uid = (int) $user->employee_code;
        if (!$uid) {
            $uid = (int) $user->id;
        }

        // For each device, create / update a DeviceFingerprint and dispatch the push job
        foreach (Device::all() as $device) {
            if (!$device->ip_address) {
                continue;
            }

            $df = DeviceFingerprint::updateOrCreate(
                [
                    // UNIQUE KEY (must match migration unique index)
                    'device_ip'    => $device->ip_address,
                    'device_uid'   => $uid,
                    'finger_index' => $data['finger_index'] ?? 0,
                ],
                [
                    'user_code'       => $user->employee_code,
                    'user_name'       => $user->name,
                    'template_base64' => base64_encode($fp->template), // decrypted by cast
                    'template_type'   => 'zk',
                ]
            );

            // Now dispatch job with DeviceFingerprint ID (correct)
            PushTemplateToDevice::dispatch($device->id, $df->id);
        }
    }

    return redirect()->route('users.index');
}


    public function show(User $user){ return view('users.show', compact('user')); }
}
