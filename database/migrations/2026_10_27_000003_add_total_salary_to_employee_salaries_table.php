<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_salaries') && ! Schema::hasColumn('employee_salaries', 'total_salary')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->decimal('total_salary', 12, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_salaries') && Schema::hasColumn('employee_salaries', 'total_salary')) {
            Schema::table('employee_salaries', function (Blueprint $table) {
                $table->dropColumn('total_salary');
            });
        }
    }
};
