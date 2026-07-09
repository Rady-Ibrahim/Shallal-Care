<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_number', 32);
            $table->foreignId('registered_branch_id')->nullable()->constrained('doctor_branches')->nullOnDelete();
            $table->timestamps();

            $table->unique(['doctor_id', 'patient_id']);
            $table->unique(['doctor_id', 'file_number']);
            $table->index('file_number');
        });

        Schema::create('clinic_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 32);
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('doctor_branches')->cascadeOnDelete();
            $table->foreignId('clinic_patient_id')->constrained('clinic_patients')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->date('visit_date');
            $table->enum('status', [
                'scheduled',
                'checked_in',
                'waiting',
                'with_doctor',
                'completed',
                'cancelled',
                'no_show',
            ])->default('scheduled');
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'waived'])->default('pending');
            $table->string('payment_method', 32)->nullable();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('queue_position')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'booking_number']);
            $table->index(['branch_id', 'visit_date', 'status']);
            $table->index('booking_number');
        });

        Schema::create('branch_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('doctor_branches')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('clinic_booking_id')->nullable()->constrained('clinic_bookings')->nullOnDelete();
            $table->enum('type', ['income', 'expense'])->default('income');
            $table->string('category', 64)->default('consultation');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 32)->default('cash');
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_transactions');
        Schema::dropIfExists('clinic_bookings');
        Schema::dropIfExists('clinic_patients');
    }
};
