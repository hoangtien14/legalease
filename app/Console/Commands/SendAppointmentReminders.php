<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:remind';
    protected $description = 'Send reminders for confirmed appointments starting within 24 hours.';

    public function handle(): int
    {
        $now = now();
        $until = $now->copy()->addDay();
        $sent = 0;

        Appointment::with(['customer.user', 'lawyer.user'])
            ->whereIn('status', ['confirmed', 'rescheduled'])
            ->whereBetween('appointment_at', [$now, $until])
            ->orderBy('appointment_at')
            ->chunkById(100, function ($appointments) use (&$sent) {
                foreach ($appointments as $appointment) {
                    if (!$appointment->customer?->user || !$appointment->lawyer?->user) continue;
                    $time = $appointment->appointment_at->format('d/m/Y H:i');
                    $customerMessage = 'Lịch tư vấn với luật sư ' . $appointment->lawyer->user->name . ' sẽ diễn ra lúc ' . $time . '.';
                    $lawyerMessage = 'Lịch tư vấn với khách hàng ' . $appointment->customer->user->name . ' sẽ diễn ra lúc ' . $time . '.';
                    $this->sendOnce($appointment->customer->user, $appointment, $customerMessage, route('customer.appointments.show', $appointment), $sent);
                    $this->sendOnce($appointment->lawyer->user, $appointment, $lawyerMessage, route('lawyer.dashboard'), $sent);
                }
            });

        $this->info("Đã gửi {$sent} thông báo nhắc lịch.");
        return self::SUCCESS;
    }

    private function sendOnce($user, Appointment $appointment, string $message, string $url, int &$sent): void
    {
        $exists = $user->notifications()
            ->where('type', 'reminder')
            ->where('message', $message)
            ->where('created_at', '>=', $appointment->appointment_at->copy()->subDay())
            ->where('created_at', '<=', now())
            ->exists();
        if ($exists) return;
        NotificationService::send($user, 'Nhắc lịch tư vấn', $message, 'reminder', $url);
        $sent++;
    }

}
