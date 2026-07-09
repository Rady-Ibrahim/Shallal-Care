<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_patients', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('file_number');
            $table->text('allergies')->nullable()->after('registered_branch_id');
            $table->text('chronic_conditions')->nullable()->after('allergies');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            if (! Schema::hasColumn('medical_records', 'clinic_booking_id')) {
                $table->foreignId('clinic_booking_id')->nullable()->after('patient_id')
                    ->constrained('clinic_bookings')->nullOnDelete();
                $table->unique('clinic_booking_id');
            }
        });

        if (Schema::hasColumn('medical_records', 'appointment_id')) {
            $this->makeAppointmentIdNullable();
        }

        $this->backfillQrTokens();
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            if (Schema::hasColumn('medical_records', 'clinic_booking_id')) {
                $table->dropConstrainedForeignId('clinic_booking_id');
            }
        });

        Schema::table('clinic_patients', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'allergies', 'chronic_conditions']);
        });
    }

    private function makeAppointmentIdNullable(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql') {
            return;
        }

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'medical_records'
              AND COLUMN_NAME = 'appointment_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement('ALTER TABLE medical_records DROP FOREIGN KEY `'.$fk->CONSTRAINT_NAME.'`');
        }

        $indexes = DB::select("SHOW INDEX FROM medical_records WHERE Column_name = 'appointment_id' AND Non_unique = 0");

        foreach ($indexes as $index) {
            if ($index->Key_name === 'PRIMARY') {
                continue;
            }
            DB::statement('ALTER TABLE medical_records DROP INDEX `'.$index->Key_name.'`');
        }

        DB::statement('ALTER TABLE medical_records MODIFY appointment_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE medical_records ADD CONSTRAINT medical_records_appointment_id_foreign FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL');
    }

    private function backfillQrTokens(): void
    {
        $ids = DB::table('clinic_patients')->whereNull('qr_token')->pluck('id');

        foreach ($ids as $id) {
            DB::table('clinic_patients')->where('id', $id)->update([
                'qr_token' => Str::random(32),
            ]);
        }
    }
};
