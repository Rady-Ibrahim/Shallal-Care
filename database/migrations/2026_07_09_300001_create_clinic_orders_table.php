<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32);
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('doctor_branches')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('clinic_patient_id')->nullable()->constrained('clinic_patients')->nullOnDelete();
            $table->foreignId('clinic_booking_id')->nullable()->constrained('clinic_bookings')->nullOnDelete();
            $table->enum('type', ['lab', 'radiology']);
            $table->enum('status', [
                'ordered',
                'sample_collected',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('ordered');
            $table->json('tests');
            $table->text('clinical_notes')->nullable();
            $table->text('result_summary')->nullable();
            $table->json('result_files')->nullable();
            $table->foreignId('ordered_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'order_number']);
            $table->index(['branch_id', 'type', 'status', 'created_at']);
            $table->index(['patient_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_orders');
    }
};
