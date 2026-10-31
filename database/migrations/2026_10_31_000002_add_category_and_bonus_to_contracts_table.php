<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addCategory = ! Schema::hasColumn('contracts', 'contract_category');
        $addBonusEnabled = ! Schema::hasColumn('contracts', 'bonus_enabled');
        $addBonusPercentage = ! Schema::hasColumn('contracts', 'bonus_percentage');

        if ($addCategory || $addBonusEnabled || $addBonusPercentage) {
            Schema::table('contracts', function (Blueprint $table) use ($addCategory, $addBonusEnabled, $addBonusPercentage): void {
                if ($addCategory) {
                    $table->string('contract_category', 30)->default('regular_contract');
                }
                if ($addBonusEnabled) {
                    $table->boolean('bonus_enabled')->default(false);
                }
                if ($addBonusPercentage) {
                    $table->decimal('bonus_percentage', 5, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $columns = array_filter(
            ['contract_category', 'bonus_enabled', 'bonus_percentage'],
            fn (string $column): bool => Schema::hasColumn('contracts', $column)
        );
        if ($columns !== []) {
            Schema::table('contracts', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
