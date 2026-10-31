<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_salaries') && ! Schema::hasColumn('employee_salaries', 'contract_bonus_amount')) {
            Schema::table('employee_salaries', function (Blueprint $table): void {
                $table->decimal('contract_bonus_amount', 12, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_salaries') && Schema::hasColumn('employee_salaries', 'contract_bonus_amount')) {
            Schema::table('employee_salaries', function (Blueprint $table): void {
                $table->dropColumn('contract_bonus_amount');
            });
        }
    }
};
