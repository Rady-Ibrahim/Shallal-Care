<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_bookings', function (Blueprint $table) {
            $table->unsignedInteger('daily_number')->nullable()->after('booking_number');
            $table->unique(['branch_id', 'visit_date', 'daily_number'], 'clinic_bookings_branch_date_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_bookings', function (Blueprint $table) {
            $table->dropUnique('clinic_bookings_branch_date_daily_unique');
            $table->dropColumn('daily_number');
        });
    }
};
