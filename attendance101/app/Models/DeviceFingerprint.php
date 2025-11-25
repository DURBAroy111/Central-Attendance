<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceFingerprint extends Model
{
    protected $table = 'device_fingerprints';

    protected $fillable = [
        'device_ip',
        'device_uid',
        'user_code',
        'user_name',
        'finger_index',
        'template_base64',
        'template_type',
    ];
}
