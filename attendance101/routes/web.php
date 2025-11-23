<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use Rats\Zkteco\Lib\ZKTeco;


Route::get('/', function(){ return redirect('/devices'); });
Route::resource('devices', DeviceController::class)->only(['index','create','store','show']);
Route::resource('users', UserController::class)->only(['index','create','store','show']);
Route::get('logs', [AttendanceController::class,'index']);


$device_ip = '192.168.1.237'; 

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