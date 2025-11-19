<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fingerprint;

class EnrollController extends Controller
{
    public function enroll(Request $r){
        $data = $r->validate(['employee_code'=>'required','name'=>'required','template'=>'required','finger_index'=>'nullable']);
        $user = User::firstOrCreate(['employee_code'=>$data['employee_code']], ['name'=>$data['name']]);
        $fp = Fingerprint::create(['user_id'=>$user->id,'template'=>$data['template'],'template_type'=>'zk','finger_index'=>$data['finger_index'] ?? 0]);
        foreach(\App\Models\Device::all() as $d){ \App\Jobs\PushTemplateToDevice::dispatch($d->id, $fp->id); }
        return response()->json(['status'=>'ok','user_id'=>$user->id]);
    }
}
