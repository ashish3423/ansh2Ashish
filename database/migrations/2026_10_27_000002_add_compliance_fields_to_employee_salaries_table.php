<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'working_days',
        'eligibility_salary',
        'bonus_amount',
        'epf_employee_contribution',
        'epf_employer_contribution',
        'esi_employee_contribution',
        'esi_employer_contribution',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('employee_salaries')) {
            return;
        }

        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->decimal('working_days', 5, 2)->default(0);
            $table->decimal('eligibility_salary', 12, 2)->default(0);
            $table->decimal('bonus_amount', 12, 2)->default(0);
            $table->decimal('epf_employee_contribution', 12, 2)->default(0);
            $table->decimal('epf_employer_contribution', 12, 2)->default(0);
            $table->decimal('esi_employee_contribution', 12, 2)->default(0);
            $table->decimal('esi_employer_contribution', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_salaries')) {
            return;
        }

        $columns = array_filter(self::COLUMNS, fn (string $column): bool => Schema::hasColumn('employee_salaries', $column));
        if ($columns !== []) {
            Schema::table('employee_salaries', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
