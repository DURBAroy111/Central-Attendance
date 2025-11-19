<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    public function index(){
        $devices = Device::orderBy('created_at','desc')->get();
        return view('devices.index', compact('devices'));
    }

    public function create(){ return view('devices.create'); }

    public function store(Request $r){
        $data = $r->validate(['name'=>'required','ip_address'=>'nullable','port'=>'nullable','serial_number'=>'nullable']);
        Device::create($data);
        return redirect()->route('devices.index');
    }

    public function show(Device $device){
        return view('devices.show', compact('device'));
    }
}
