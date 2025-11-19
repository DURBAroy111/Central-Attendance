<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model {
    protected $fillable = ['name','ip_address','port','serial_number','model','is_online','last_seen_at'];
    public function fingerprints(){ return $this->hasMany(\App\Models\Fingerprint::class, 'registered_device_id'); }
}
