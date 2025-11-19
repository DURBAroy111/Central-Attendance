<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(){
        Schema::create('fingerprints', function(Blueprint $t){
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('template');
            $t->string('template_type')->default('zk');
            $t->integer('finger_index')->nullable();
            $t->foreignId('registered_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('fingerprints'); }
};
