<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_type', 30);
            $table->string('registration_number', 50)->unique();
            $table->string('make_model', 150)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['vehicle_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_vehicles');
    }
};
