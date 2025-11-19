<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EnrollController;

Route::post('/enroll', [EnrollController::class, 'enroll']);
