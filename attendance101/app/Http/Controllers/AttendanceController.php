<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;

class AttendanceController extends Controller
{
    public function index(){
        $logs = AttendanceLog::with(['user','device'])->orderBy('timestamp','desc')->paginate(50);
        return view('logs.index', compact('logs'));
    }
}
