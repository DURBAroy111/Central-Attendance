<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip')->unique();
            $table->unsignedInteger('port')->default(4370);
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('firmware')->nullable();
            $table->string('status')->default('offline'); // online/offline/unknown
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('push_port')->nullable();
            $table->unsignedInteger('sdk_port')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
