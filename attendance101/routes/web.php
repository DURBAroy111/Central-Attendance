<?php

use App\Models\DeviceFingerprint;
use Rats\Zkteco\Lib\ZKTeco;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;



Route::get('/', function(){ return redirect('/devices'); });
Route::resource('devices', DeviceController::class)->only(['index','create','store','show','edit', 'update', 'destroy']);
Route::post('/devices/{device}/ping', [DeviceController::class, 'pingNow'])
    ->name('devices.ping');
Route::resource('users', UserController::class)->only(['index','create','store','show']);
Route::get('logs', [AttendanceController::class,'index']);

Route::get('/devices/{device}/debug-push', [DeviceController::class, 'debugPush'])->name('devices.debug-push');


$device_ip = '192.168.1.8'; 
//ping 192.168.1.8

// Pull all attendance logs from device
Route::get('/device/pull-logs', function () use ($device_ip) {
    $zk = new ZKTeco($device_ip);

    if ($zk->connect()) {
        $attendance = $zk->getAttendance();
        return response()->json($attendance);
    } else {
        return "Cannot connect to device";
    }
});

// Pull all users from device
Route::get('/device/pull-users', function () use ($device_ip) {
    $zk = new ZKTeco($device_ip);

    if ($zk->connect()) {
        $users = $zk->getUser();
        return response()->json($users);
    } else {
        return "Cannot connect to device";
    }
});

Route::get('/device/fingerprints', function () {

    set_time_limit(3000);

    $device_ip = '192.168.1.8';
    $port = 4370;


    $zk = new ZKTeco($device_ip, $port);

    if (! $zk->connect()) {
        return "Cannot connect to device at {$device_ip}";
    }

    try { $zk->disableDevice(); } catch (\Throwable $e) {}

    $users = $zk->getUser();

    if (empty($users)) {
        return "No users found on device.";
    }

    $allFingerprints = [];

    foreach ($users as $u) {

        $deviceUid = $u['uid'];
        $userCode  = $u['userid'];
        $userName  = $u['name'] ?? '';

        try {
            $fingerprints = $zk->getFingerprint($deviceUid);
        } catch (\Throwable $e) {
            continue;
        }

        if (empty($fingerprints)) continue;

        foreach ($fingerprints as $fingerIndex => $tplData) {

            if (is_array($tplData) && isset($tplData['tpl'])) {
                $binary = $tplData['tpl'];
            } else {
                $binary = $tplData;
            }

            if (! $binary) continue;

            $base64 = base64_encode($binary);

            // 🟢 SAVE DIRECTLY IN DATABASE
            DeviceFingerprint::updateOrCreate(
                [
                    'device_ip'    => $device_ip,
                    'device_uid'   => $deviceUid,
                    'finger_index' => $fingerIndex,
                ],
                [
                    'user_code'      => $userCode,
                    'user_name'      => $userName,
                    'template_base64'=> $base64,
                    'template_type'  => 'zk',
                ]
            );

            // For show in page:
            $allFingerprints[] = [
                'device_uid' => $deviceUid,
                'user_code'  => $userCode,
                'user_name'  => $userName,
                'finger_index' => $fingerIndex,
                'template'   => $base64,
            ];
        }
    }

    try { $zk->enableDevice(); } catch (\Throwable $e) {}
    $zk->disconnect();

    // Show result on screen
    return response()->json([
        'saved' => count($allFingerprints),
        'data'  => $allFingerprints
    ]);
});



// // Clear all attendance logs on the device
// Route::get('/device/clear-logs', function () use ($device_ip) {
//     $zk = new ZKTeco($device_ip);

//     if ($zk->connect()) {
//         $zk->clearAttendance();
//         return "Device logs cleared!";
//     } else {
//         return "Cannot connect to device";
//     }
// });