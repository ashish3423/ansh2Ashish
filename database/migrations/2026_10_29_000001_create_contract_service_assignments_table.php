<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_service_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('service_vehicle_id')->nullable()->constrained('service_vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('assistant_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->index(['contract_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_service_assignments');
    }
};
