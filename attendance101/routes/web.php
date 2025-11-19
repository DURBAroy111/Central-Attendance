<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;

Route::get('/', function(){ return redirect('/devices'); });
Route::resource('devices', DeviceController::class)->only(['index','create','store','show']);
Route::resource('users', UserController::class)->only(['index','create','store','show']);
Route::get('logs', [AttendanceController::class,'index']);
