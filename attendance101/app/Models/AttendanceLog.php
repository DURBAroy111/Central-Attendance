<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model {
    protected $fillable = ['user_id','device_id','device_user_id','timestamp','verification_mode','raw_payload'];
    protected $casts = ['raw_payload' => 'array','timestamp' => 'datetime'];
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
    public function device(){ return $this->belongsTo(\App\Models\Device::class); }
}
