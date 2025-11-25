<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_fingerprints', function (Blueprint $table) {
            $table->id();

            // Device info
            $table->string('device_ip')->nullable();

            // User as stored on device
            $table->unsignedInteger('device_uid');   // internal numeric id on device
            $table->string('user_code');            // userid from device 
            $table->string('user_name')->nullable();

            // Finger
            $table->unsignedTinyInteger('finger_index')->nullable();

            // Biometric template (base64 string)
            $table->longText('template_base64');
            $table->string('template_type')->default('zk'); // e.g. 'zk'

            $table->timestamps();

            
            $table->unique(
                ['device_ip', 'device_uid', 'finger_index'],
                'device_fingerprints_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_fingerprints');
    }
};
