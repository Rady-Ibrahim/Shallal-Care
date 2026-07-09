<?php

namespace Database\Seeders;

use App\Support\PhoneNormalizer;
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
 * Demo data for mobile API + clinic web testing (Egypt).
 *
 * Mobile patient login: 01088880000 / password123
 * Mobile guest: POST /api/v1/auth/guest { "device_id": "demo-device-001" }
 * Doctor web: 01088880001 / password123
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        AppSetting::updatePaymentSettings([
            'vodafone_cash_number' => '0108888099',
            'bank_name' => 'البنك الأهلي المصري',
            'bank_account_name' => 'Shallal Care',
            'bank_account_number' => 'EG12345678901234567890',
        ]);

        $cairo = Governorate::where('name_en', 'Cairo')->first()
            ?? Governorate::where('is_active', true)->first();

        $alex = Governorate::where('name_en', 'Alexandria')->first();

        $general = Speciality::firstOrCreate(
            ['name_ar' => 'طب عام'],
            ['name_en' => 'General Medicine', 'is_active' => true]
        );

        $cardio = Speciality::firstOrCreate(
            ['name_ar' => 'أمراض القلب'],
            ['name_en' => 'Cardiology', 'is_active' => true]
        );

        $patientPhone = PhoneNormalizer::toE164('01088880000');
        $doctorPhone = PhoneNormalizer::toE164('01088880001');
        $doctor2Phone = PhoneNormalizer::toE164('01088880002');

        $doctorUser = User::updateOrCreate(
            ['phone' => $doctorPhone],
            [
                'name' => 'د. أحمد محمد',
                'email' => 'doctor@shallal-care.test',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $doctor2User = User::updateOrCreate(
            ['phone' => $doctor2Phone],
            [
                'name' => 'د. سارة علي',
                'email' => 'doctor2@shallal-care.test',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $patient = User::updateOrCreate(
            ['phone' => $patientPhone],
            [
                'name' => 'مريض تجريبي',
                'email' => 'patient@shallal-care.test',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'status' => 'active',
                'email_verified_at' => now(),
                'birthdate' => '1995-01-15',
                'gender' => 'male',
                'city' => 'القاهرة',
                'district' => 'مدينة نصر',
            ]
        );

        $professionalPlan = Subscription::where('name', 'Premium')->first()
            ?? Subscription::where('is_featured', true)->first()
            ?? Subscription::first();

        $doctor = Doctor::updateOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'speciality_id' => $general->id,
                'clinic_name' => 'عيادة الشفاء',
                'bio_ar' => 'طبيب عام — عيادة في القاهرة',
                'experience_years' => 10,
                'consultation_fee' => 300,
                'consultation_type' => 'clinic',
                'rating' => 4.6,
                'rating_count' => 12,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
                'address' => 'القاهرة — مدينة نصر',
                'status' => 'approved',
                'subscription_id' => $professionalPlan?->id,
            ]
        );

        $doctor2 = Doctor::updateOrCreate(
            ['user_id' => $doctor2User->id],
            [
                'speciality_id' => $cardio->id,
                'clinic_name' => 'عيادة القلب',
                'bio_ar' => 'استشاري أمراض قلب — الإسكندرية',
                'experience_years' => 15,
                'consultation_fee' => 500,
                'consultation_type' => 'clinic',
                'rating' => 4.8,
                'rating_count' => 20,
                'latitude' => 31.2001,
                'longitude' => 29.9187,
                'address' => 'الإسكندرية — سموحة',
                'status' => 'approved',
                'subscription_id' => $professionalPlan?->id,
            ]
        );

        foreach ([$doctor, $doctor2] as $doc) {
            if ($professionalPlan) {
                DoctorSubscription::updateOrCreate(
                    [
                        'doctor_id' => $doc->id,
                        'subscription_id' => $professionalPlan->id,
                        'status' => 'active',
                    ],
                    [
                        'start_date' => now()->subDays(5),
                        'end_date' => now()->addDays(25),
                        'amount_paid' => $professionalPlan->price,
                        'payment_method' => 'vodafone_cash',
                        'transaction_id' => 'DEMO-TXN-'.$doc->id,
                    ]
                );
            }
        }

        $branch = DoctorBranch::updateOrCreate(
            [
                'doctor_id' => $doctor->id,
                'is_primary' => true,
            ],
            [
                'governorate_id' => $cairo?->id,
                'branch_name' => 'فرع مدينة نصر',
                'governorate' => $cairo?->name_ar ?? 'القاهرة',
                'district' => 'مدينة نصر',
                'address' => 'شارع عباس العقاد، القاهرة',
                'latitude' => 30.0626,
                'longitude' => 31.3417,
                'phone' => PhoneNormalizer::toLocal('01088880001'),
                'is_active' => true,
            ]
        );

        DoctorBranch::updateOrCreate(
            [
                'doctor_id' => $doctor2->id,
                'is_primary' => true,
            ],
            [
                'governorate_id' => $alex?->id ?? $cairo?->id,
                'branch_name' => 'فرع سموحة',
                'governorate' => $alex?->name_ar ?? 'الإسكندرية',
                'district' => 'سموحة',
                'address' => 'شارع فوزي معاذ، الإسكندرية',
                'latitude' => 31.2156,
                'longitude' => 29.9553,
                'phone' => PhoneNormalizer::toLocal('01088880002'),
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
                'price' => 300,
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
                'price' => 300,
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
                'price' => 300,
                'payment_status' => 'pending',
                'notes' => 'موعد بانتظار موافقة الطبيب',
            ]
        );

        MedicalRecord::updateOrCreate(
            ['appointment_id' => $completedAppointment->id],
            [
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'branch_id' => $branch->id,
                'record_type' => 'diagnosis',
                'diagnosis' => 'التهاب حلق بسيط',
                'prescription' => [
                    ['medicine' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'مرتين يومياً'],
                ],
                'notes' => 'راحة وشرب سوائل',
                'created_by' => $doctorUser->id,
                'weight' => 72,
                'height' => 175,
                'blood_pressure' => '120/80',
                'allergies' => 'لا يوجد',
            ]
        );

        $this->command?->info('Demo data seeded (Egypt / mobile-ready).');
        $this->command?->newLine();
        $this->command?->info('── Mobile API (password: password123) ──');
        $this->command?->info('Patient login: 01088880000');
        $this->command?->info('Guest: POST /api/v1/auth/guest  body: {"device_id":"demo-device-001"}');
        $this->command?->info('Config: GET /api/v1/mobile/config');
        $this->command?->info('Browse: GET /api/v1/doctors  |  GET /api/v1/governorates');
        $this->command?->newLine();
        $this->command?->info('── Clinic web ──');
        $this->command?->info('Doctor: 01088880001 / password123');
        $this->command?->newLine();
        $this->command?->info('── IDs for Postman ──');
        $this->command?->info("doctor_id      = {$doctor->id}");
        $this->command?->info("doctor2_id     = {$doctor2->id}");
        $this->command?->info("schedule_id    = {$sundaySchedule->id}");
        $this->command?->info("branch_id      = {$branch->id}");
        $this->command?->info("appointment_id = {$completedAppointment->id}");
        $this->command?->info('Enable booking test: MOBILE_BOOKING_ENABLED=true in .env');
    }
}
