<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(){
        Schema::create('users', function(Blueprint $t){
            $t->id();
            $t->string('employee_code')->unique();
            $t->string('name');
            $t->string('department')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('users'); }
};
