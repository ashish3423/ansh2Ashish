<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            $table->string('department', 100)->nullable();
            $table->string('organization', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('contract_type', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['department', 'organization', 'address', 'contract_type']);
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
        });
    }
};
