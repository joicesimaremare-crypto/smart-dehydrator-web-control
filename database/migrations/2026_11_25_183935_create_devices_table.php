<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            // Nama perangkat
            $table->string('name');

            // Jenis perangkat
            // Contoh: heater, fan, lamp, exhaust, relay
            $table->string('type');

            // GPIO ESP32 yang digunakan
            $table->unsignedTinyInteger('gpio');

            // Status perangkat
            // false = OFF
            // true  = ON
            $table->boolean('state')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};