<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        $addEpfEnabled = ! Schema::hasColumn('employees', 'epf_enabled');
        $addEsiEnabled = ! Schema::hasColumn('employees', 'esi_enabled');
        if ($addEpfEnabled || $addEsiEnabled) {
            Schema::table('employees', function (Blueprint $table) use ($addEpfEnabled, $addEsiEnabled): void {
                if ($addEpfEnabled) {
                    $table->boolean('epf_enabled')->default(true);
                }
                if ($addEsiEnabled) {
                    $table->boolean('esi_enabled')->default(true);
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        $columns = array_filter(
            ['epf_enabled', 'esi_enabled'],
            fn (string $column): bool => Schema::hasColumn('employees', $column)
        );
        if ($columns !== []) {
            Schema::table('employees', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
