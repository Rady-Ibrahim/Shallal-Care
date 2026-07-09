<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('clinic_name')->nullable()->after('user_id');
            $table->string('syndicate_number')->nullable()->after('experience_years');
            $table->string('signature_path')->nullable()->after('clinic_image');
            $table->string('stamp_path')->nullable()->after('signature_path');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            if (! Schema::hasColumn('medical_records', 'prescription_number')) {
                $table->string('prescription_number')->nullable()->unique()->after('record_type');
            }
            if (! Schema::hasColumn('medical_records', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('doctor_id')->constrained('doctor_branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (Schema::hasColumn('medical_records', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }
            if (Schema::hasColumn('medical_records', 'prescription_number')) {
                $table->dropColumn('prescription_number');
            }
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn(['clinic_name', 'syndicate_number', 'signature_path', 'stamp_path']);
        });
    }
};
