<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'device_uid'   => 'integer',
        'finger_index' => 'integer',
    ];

    /**
     * Relationship with device
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_ip', 'ip_address');
    }

    /**
     * Scope for specific device
     */
    public function scopeForDevice($query, $ipAddress)
    {
        return $query->where('device_ip', $ipAddress);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userCode)
    {
        return $query->where('user_code', $userCode);
    }

    /**
     * Get template binary (decoded from base64)
     */
    public function getTemplateBinaryAttribute(): string
    {
        $binary = base64_decode($this->template_base64, true);
        return $binary !== false ? $binary : $this->template_base64;
    }
}
