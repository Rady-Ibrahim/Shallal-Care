<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient','doctor','admin','secretary') NOT NULL DEFAULT 'patient'");
        }

        if (! Schema::hasTable('clinic_staff_members')) {
            Schema::create('clinic_staff_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained('doctor_branches')->cascadeOnDelete();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->json('permissions');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();

                $table->index(['doctor_id', 'branch_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_staff_members');

        if (Schema::hasTable('users')) {
            DB::table('users')->where('role', 'secretary')->delete();
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient'");
        }
    }
};
