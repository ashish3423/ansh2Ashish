<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->date('extension_date')->nullable();
            $table->decimal('paid_holidays_percentage', 5, 2)->nullable();
            $table->decimal('leave_with_wages_percentage', 5, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'extension_date',
                'paid_holidays_percentage',
                'leave_with_wages_percentage',
            ]);
        });
    }
};