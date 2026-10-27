<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_compliance_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('epf_employee_percentage', 5, 2)->nullable();
            $table->decimal('epf_employer_percentage', 5, 2)->nullable();
            $table->decimal('esi_employee_percentage', 5, 2)->nullable();
            $table->decimal('esi_employer_percentage', 5, 2)->nullable();
            $table->decimal('epf_eligibility_amount', 12, 2)->nullable();
            $table->decimal('bonus_eligibility_amount', 12, 2)->nullable();
            $table->timestamps();
        });

        DB::table('employee_compliance_settings')->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_compliance_settings');
    }
};
