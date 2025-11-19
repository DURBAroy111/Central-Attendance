<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('devices', function(Blueprint $t){
            $t->id();
            $t->string('name');
            $t->string('ip_address')->nullable();
            $t->integer('port')->default(4370);
            $t->string('serial_number')->nullable();
            $t->string('model')->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->boolean('is_online')->default(false);
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('devices'); }
};
