<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(){
        Schema::create('attendance_logs', function(Blueprint $t){
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $t->string('device_user_id')->nullable();
            $t->dateTime('timestamp');
            $t->string('verification_mode')->nullable();
            $t->json('raw_payload')->nullable();
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('attendance_logs'); }
};
