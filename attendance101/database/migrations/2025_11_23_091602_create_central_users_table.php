<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable(); // internal device uid
            $table->string('user_id')->nullable();      // employee id used on device
            $table->string('name');
            $table->string('card_number')->nullable();
            $table->string('password')->nullable();
            $table->unsignedTinyInteger('privilege')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_users');
    }
};
