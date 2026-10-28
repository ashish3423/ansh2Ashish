<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_compliance_settings', function (Blueprint $table) {
            $table->decimal('esi_eligibility_amount', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employee_compliance_settings', function (Blueprint $table) {
            $table->dropColumn('esi_eligibility_amount');
        });
    }
};
