<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(){
        Schema::create('device_sync_queue', function(Blueprint $t){
            $t->id();
            $t->foreignId('device_id')->constrained('devices')->cascadeOnDelete();
            $t->string('action');
            $t->json('payload')->nullable();
            $t->string('status')->default('pending');
            $t->integer('retry_count')->default(0);
            $t->timestamp('next_attempt_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('device_sync_queue'); }
};
