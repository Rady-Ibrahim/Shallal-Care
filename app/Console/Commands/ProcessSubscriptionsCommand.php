<?php

namespace App\Console\Commands;

use App\Notifications\SubscriptionExpiringSoonAdmin;
use App\Notifications\SubscriptionExpiryReminder;
use App\Services\AdminNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Modules\Subscription\Models\DoctorSubscription;

class ProcessSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Expire ended subscriptions and send 3-day expiry reminders';

    public function handle(): int
    {
        $expiring = DoctorSubscription::with('doctor')
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today())
            ->get();

        $expired = 0;
        foreach ($expiring as $sub) {
            $sub->update(['status' => 'expired']);
            $sub->doctor?->update(['subscription_id' => null]);
            $expired++;
        }

        $this->info("Marked {$expired} subscription(s) as expired.");

        $reminderDate = today()->addDays(3);

        $dueForReminder = DoctorSubscription::with(['doctor.user', 'subscription'])
            ->where('status', 'active')
            ->whereDate('end_date', $reminderDate)
            ->whereNull('expiry_reminder_sent_at')
            ->get();

        $sent = 0;
        foreach ($dueForReminder as $sub) {
            $doctorUser = $sub->doctor?->user;
            $email = $doctorUser?->email;
            $planName = $sub->subscription?->name ?? 'اشتراكك';
            $endDate = $sub->end_date?->format('Y-m-d');

            if ($email) {
                try {
                    Mail::raw(
                        "تنبيه: اشتراكك في باقة \"{$planName}\" سينتهي بتاريخ {$endDate}. يرجى تجديد الاشتراك من لوحة تحكم الطبيب.",
                        fn ($message) => $message->to($email)->subject('تنبيه: اشتراكك على وشك الانتهاء — Shallal Care')
                    );
                } catch (\Throwable $e) {
                    $this->warn("Failed to email {$email}: {$e->getMessage()}");
                }
            }

            if ($doctorUser) {
                $doctorUser->notify(new SubscriptionExpiryReminder($sub));
            }

            AdminNotificationService::notify(new SubscriptionExpiringSoonAdmin($sub));

            $sub->update(['expiry_reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} expiry reminder(s).");

        return self::SUCCESS;
    }
}
