<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fingerprint extends Model {
    protected $fillable = ['user_id','template','template_type','finger_index','registered_device_id'];
    protected $casts = [ 'template' => 'encrypted' ];
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
    public function device(){ return $this->belongsTo(\App\Models\Device::class, 'registered_device_id'); }
}
