<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'model',
        'serial_number',
        'firmware',
        'status',      
        'last_seen_at',
        'push_port',
        'sdk_port',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

   
    public static function ping(string $ip, int $port = 4370, float $timeout = 1.0): bool
    {
        $errno  = 0;
        $errstr = '';

        $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);

        if ($conn) {
            fclose($conn);
            return true;
        }

        return false;
    }

    /**
     * Convenience accessor: $device->is_online
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->status === 'online';
    }
}
