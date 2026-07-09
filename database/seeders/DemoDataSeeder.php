<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\AppSetting;
use Modules\Appointment\Models\Appointment;
use Modules\Auth\Models\User;
use Modules\Doctor\Models\Doctor;
use Modules\Doctor\Models\DoctorBranch;
use Modules\Doctor\Models\DoctorSchedule;
use Modules\Doctor\Models\Governorate;
use Modules\Doctor\Models\Speciality;
use Modules\MedicalRecord\Models\MedicalRecord;
use Modules\Subscription\Models\DoctorSubscription;
use Modules\Subscription\Models\Subscription;

/**
 * Demo data for CMS / API testing.
 *
 * Patient API login: 07708888000 / password123  (patient@shallal-care.test)
 * Doctor web login:  07708888001 / password123  (doctor@shallal-care.test)
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::updatePaymentSettings([
            'vodafone_cash_number' => '07708888099',
            'bank_name' => 'مصرف الرافدين',
            'bank_account_name' => 'Shallal Care',
            'bank_account_number' => 'IQ12RAFB1234567890',
        ]);

        $baghdad = Governorate::where('name_en', 'Baghdad')->first()
            ?? Governorate::where('is_active', true)->first();

        $speciality = Speciality::firstOrCreate(
            ['name_ar' => 'طب عام'],
            ['name_en' => 'General Medicine', 'is_active' => true]
        );

        Speciality::firstOrCreate(
            ['name_ar' => 'أمراض القلب'],
            ['name_en' => 'Cardiology', 'is_active' => true]
        );

        $doctorUser = User::updateOrCreate(
            ['phone' => '07708888001'],
            [
                'name' => 'د. أحمد محمد',
                'email' => 'doctor@shallal-care.test',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $patient = User::updateOrCreate(
            ['phone' => '07708888000'],
            [
                'name' => 'مريض تجريبي',
                'email' => 'patient@shallal-care.test',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'status' => 'active',
                'email_verified_at' => now(),
                'birthdate' => '1995-01-15',
                'gender' => 'male',
                'city' => 'بغداد',
                'district' => 'الكرادة',
            ]
        );

        $professionalPlan = Subscription::where('name', 'Premium')->first()
            ?? Subscription::where('is_featured', true)->first()
            ?? Subscription::first();

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'speciality_id' => $speciality->id,
                'bio_ar' => 'طبيب عام بخبرة 10 سنوات في بغداد',
                'experience_years' => 10,
                'consultation_fee' => 25000,
                'consultation_type' => 'clinic',
                'rating' => 4.5,
                'rating_count' => 8,
                'latitude' => 33.3152,
                'longitude' => 44.3661,
                'address' => 'بغداد - الكرادة',
                'status' => 'approved',
                'subscription_id' => $professionalPlan?->id,
            ]
        );

        if ($professionalPlan) {
            DoctorSubscription::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'subscription_id' => $professionalPlan->id,
                    'status' => 'active',
                ],
                [
                    'start_date' => now()->subDays(5),
                    'end_date' => now()->addDays(25),
                    'amount_paid' => $professionalPlan->price,
                    'payment_method' => 'vodafone_cash',
                    'transaction_id' => 'DEMO-TXN-001',
                ]
            );
        }

        $branch = DoctorBranch::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'is_primary' => true,
            ],
            [
                'governorate_id' => $baghdad?->id,
                'branch_name' => 'العيادة الرئيسية',
                'governorate' => $baghdad?->name_ar ?? 'بغداد',
                'district' => 'الكرادة',
                'address' => 'شارع أبو نواس، بغداد',
                'latitude' => 33.3152,
                'longitude' => 44.3661,
                'phone' => '07708888001',
                'is_active' => true,
            ]
        );

        $scheduleDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        $schedules = [];

        foreach ($scheduleDays as $day) {
            $schedules[$day] = DoctorSchedule::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'is_active' => true,
                    'doctor_branch_id' => $branch->id,
                ]
            );
        }

        $sundaySchedule = $schedules['Sunday'];

        $completedAppointment = Appointment::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => today()->subDays(3),
                'appointment_time' => '10:00:00',
            ],
            [
                'doctor_schedule_id' => $sundaySchedule->id,
                'status' => 'completed',
                'price' => 25000,
                'payment_status' => 'paid',
                'notes' => 'موعد تجريبي مكتمل',
            ]
        );

        $confirmedAppointment = Appointment::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => today()->addDays(2),
                'appointment_time' => '11:30:00',
            ],
            [
                'doctor_schedule_id' => $sundaySchedule->id,
                'status' => 'confirmed',
                'price' => 25000,
                'payment_status' => 'pending',
                'notes' => 'موعد قادم مؤكد',
            ]
        );

        $pendingAppointment = Appointment::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => today()->addDays(5),
                'appointment_time' => '14:00:00',
            ],
            [
                'doctor_schedule_id' => $schedules['Monday']->id,
                'status' => 'pending',
                'price' => 25000,
                'payment_status' => 'pending',
                'notes' => 'موعد بانتظار موافقة الطبيب',
            ]
        );

        MedicalRecord::updateOrCreate(
            ['appointment_id' => $completedAppointment->id],
            [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'record_type' => 'diagnosis',
                'diagnosis' => 'التهاب حلق بسيط',
                'prescription' => [
                    ['medicine' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'مرتين يومياً'],
                ],
                'notes' => 'راحة لمدة يومين وشرب سوائل دافئة',
                'created_by' => $doctorUser->id,
                'weight' => 72,
                'height' => 175,
                'blood_pressure' => '120/80',
                'allergies' => 'لا يوجد',
            ]
        );

        $this->command?->info('Demo data seeded successfully.');
        $this->command?->newLine();
        $this->command?->info('── Postman / API accounts (password: password123) ──');
        $this->command?->info('Patient: 07708888000  |  patient@shallal-care.test');
        $this->command?->info('Doctor web login: 07708888001 / password123 — لرؤية المواعيد التجريبية');
        $this->command?->info('Admin: أضف/عدّل إعدادات الدفع من /admin/dashboard/subscriptions');
        $this->command?->newLine();
        $this->command?->info('── Postman collection variables ──');
        $this->command?->info("doctor_id      = {$doctor->id}");
        $this->command?->info("schedule_id    = {$sundaySchedule->id}");
        $this->command?->info("branch_id      = {$branch->id}");
        $this->command?->info("appointment_id = {$completedAppointment->id} (completed — for history/review)");
        $this->command?->info("appointment_id_confirmed = {$confirmedAppointment->id}");
        $this->command?->info("appointment_id_pending   = {$pendingAppointment->id}");
    }
}
