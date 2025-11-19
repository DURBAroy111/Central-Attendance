<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $fillable = ['employee_code','name','department','is_active'];
    public function fingerprints(){ return $this->hasMany(\App\Models\Fingerprint::class); }
}
