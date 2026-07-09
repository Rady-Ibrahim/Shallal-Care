<?php

namespace Modules\Auth\Services\Api;

use Modules\Auth\Models\User;
use Modules\Auth\Models\Otp;
use Modules\Doctor\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $emailVerifiedAt = null;

            if (!empty($data['email'])) {
                $hasVerifiedOtp = Otp::where('email', $data['email'])
                    ->where('type', 'register')
                    ->whereNotNull('verified_at')
                    ->where('verified_at', '>=', now()->subHour())
                    ->exists();

                if ($hasVerifiedOtp) {
                    $emailVerifiedAt = now();
                    Otp::where('email', $data['email'])->where('type', 'register')->delete();
                }
            }

            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'patient',
                'status' => 'active',
                'email_verified_at' => $emailVerifiedAt,
            ]);

            return $user;
        });
    }

    /**
     * Login with phone or email + password.
     */
    public function login(string $identifier, string $password): ?User
    {
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'phone';
    
        $user = User::where($field, $identifier)->first();
    
        if (!$user) {
            return null;
        }
    
        if (!Hash::check($password, $user->password)) {
            return null;
        }
    
        if (!$user->isActive()) {
            return null;
        }
    
        return $user;
    }

    /**
     * Send OTP via email (if provided) or SMS.
     */
    public function sendOtp(?string $phone, string $type = 'login', ?string $email = null): Otp
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($email) {
            Otp::where('email', $email)->where('type', $type)->delete();
        } else {
            Otp::where('phone', $phone)->where('type', $type)->delete();
        }

        $otp = Otp::create([
            'email'      => $email,
            'code'       => $code,
            'type'       => $type,
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($email) {
            $this->sendOtpByEmail($email, $code, $type);
        }

        return $otp;
    }

    protected function sendOtpByEmail(string $email, string $code, string $type): void
    {
        $subject = match($type) {
            'password_reset' => 'كود إعادة تعيين كلمة المرور',
            'register'       => 'كود تفعيل الحساب',
            default          => 'كود التحقق',
        };

        Mail::raw(
            "كود التحقق الخاص بك هو: {$code}",
            function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            }
        );
    }

    public function verifyOtp(?string $phone, string $code, string $type = 'login', ?string $email = null): ?Otp
    {
        $type = match ($type) {
            'reset_password' => 'password_reset',
            default => $type,
        };

        $query = Otp::where('type', $type)->latest();

        if ($email) {
            $query->where('email', $email);
        } else {
            $query->where('phone', $phone);
        }

        $otp = $query->first();

        if (!$otp || $otp->isExpired() || $otp->isMaxAttemptsExceeded()) {
            return null;
        }

        if ($otp->code !== $code) {
            $otp->increment('attempts');
            return null;
        }

        $otp->update(['verified_at' => now()]);

        return $otp->fresh();
    }

    public function loginWithOtp(string $phone, string $code): ?User
    {
        $otp = $this->verifyOtp($phone, $code, 'login');

        if (!$otp) {
            return null;
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return null;
        }

        $otp->delete();

        return $user;
    }

    public function resetPassword(string $phone, string $code, string $newPassword): ?User
    {
        return DB::transaction(function () use ($phone, $code, $newPassword) {
            $otp = $this->verifyOtp($phone, $code, 'password_reset');

            if (!$otp) {
                return null;
            }

            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return null;
            }

            $user->update(['password' => Hash::make($newPassword)]);

            $otp->delete();

            return $user;
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update([
                'name'      => $data['name']     ?? $user->name,
                'email'     => $data['email']    ?? $user->email,
                'phone'     => $data['phone']    ?? $user->phone,
                'birthdate' => $data['birthdate'] ?? $user->birthdate,
                'gender'    => $data['gender']   ?? $user->gender,
                'city'      => $data['city']     ?? $user->city,
                'district'  => $data['district'] ?? $user->district,
                'address'   => $data['address']  ?? $user->address,
            ]);

            if ($user->isDoctor()) {
                $doctor = \Modules\Doctor\Models\Doctor::where('user_id', $user->id)->first();
                if ($doctor) {
                    $doctor->update([
                        'bio_ar'           => $data['bio']              ?? $doctor->bio_ar,
                        'experience_years' => $data['experience_years'] ?? $doctor->experience_years,
                    ]);
                }
            }

            return $user->fresh();
        });
    }

    public function updatePassword(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);
        return $user->fresh();
    }

    public function forgotPassword(string $phone): bool
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return false;
        }

        $this->sendOtp($phone, 'password_reset');

        return true;
    }

    public function createToken(User $user, string $name = 'auth_token'): string
    {
        return $user->createToken($name)->plainTextToken;
    }

    public function loginAsGuest(string $deviceId): User
    {
        $user = User::where('guest_device_id', $deviceId)->first();

        if ($user) {
            if (! $user->isActive()) {
                throw new \RuntimeException('حساب الزائر غير نشط');
            }

            return $user;
        }

        return User::create([
            'name' => config('mobile.guest.name', 'زائر'),
            'phone' => $this->syntheticGuestPhone($deviceId),
            'password' => Hash::make(Str::random(48)),
            'role' => 'patient',
            'status' => 'active',
            'is_guest' => true,
            'guest_device_id' => $deviceId,
            'email_verified_at' => now(),
        ]);
    }

    private function syntheticGuestPhone(string $deviceId): string
    {
        $suffix = str_pad((string) (abs(crc32($deviceId)) % 100000000), 8, '0', STR_PAD_LEFT);

        return '+20998'.$suffix;
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    public function getDoctorStats(User $user): array
    {
        if (!$user->isDoctor()) {
            return [];
        }

        $doctor = \Modules\Doctor\Models\Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return [];
        }

        $totalPatientsCount = \Modules\Appointment\Models\Appointment::where('doctor_id', $doctor->id)
            ->distinct('patient_id')
            ->count();

        $sentPrescriptionsCount = \Modules\MedicalRecord\Models\MedicalRecord::whereHas('appointment', function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id);
        })->where('record_type', 'prescription')->count();

        $todayAppointmentsCount = \Modules\Appointment\Models\Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', now()->toDateString())
            ->count();

        return [
            'total_patients_count' => $totalPatientsCount,
            'sent_prescriptions_count' => $sentPrescriptionsCount,
            'today_appointments_count' => $todayAppointmentsCount,
        ];
    }

    /**
     * Create a new admin user (Dashboard only)
     */
    public function createAdmin(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $admin = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin',
                'status' => 'active',
            ]);

            return $admin;
        });
    }

    /**
     * Create a new doctor user (Dashboard only)
     */
    public function createDoctor(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => !empty($data['email']) ? null : now(),
            ]);

            Doctor::create([
                'user_id' => $user->id,
                'speciality_id' => $data['speciality_id'] ?? null,
                'bio_ar' => $data['bio_ar'] ?? null,
                'bio_en' => $data['bio_en'] ?? null,
                'experience_years' => $data['experience_years'] ?? 0,
                'status' => 'pending',
            ]);

            return $user;
        });
    }
}
