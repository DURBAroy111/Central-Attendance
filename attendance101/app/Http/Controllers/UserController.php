<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fingerprint;

class UserController extends Controller
{
    public function index(){
        $users = User::with('fingerprints')->orderBy('created_at','desc')->get();
        return view('users.index', compact('users'));
    }

    public function create(){ return view('users.create'); }

    public function store(Request $r){
        $data = $r->validate(['employee_code'=>'required','name'=>'required','template'=>'nullable','finger_index'=>'nullable']);
        $user = User::create(['employee_code'=>$data['employee_code'],'name'=>$data['name']]);
        if(!empty($data['template'])){
            $fp = Fingerprint::create(['user_id'=>$user->id,'template'=>$data['template'],'template_type'=>'zk','finger_index'=>$data['finger_index'] ?? 0]);
            foreach(\App\Models\Device::all() as $d){ \App\Jobs\PushTemplateToDevice::dispatch($d->id, $fp->id); }
        }
        return redirect()->route('users.index');
    }

    public function show(User $user){ return view('users.show', compact('user')); }
}
