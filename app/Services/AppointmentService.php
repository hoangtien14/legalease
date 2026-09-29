<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\LawyerProfile;
use App\Models\CustomerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    private const ACTIVE_STATUSES = ['pending', 'confirmed', 'rescheduled'];

    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled', 'rescheduled'],
        'confirmed' => ['completed', 'cancelled', 'rescheduled'],
        'rescheduled' => ['confirmed', 'completed', 'cancelled', 'rescheduled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function book(CustomerProfile $customer, LawyerProfile $lawyer, array $data, ?string $attachment = null): Appointment
    {
        return DB::transaction(function () use ($customer, $lawyer, $data, $attachment) {
            $customer = CustomerProfile::query()->with('user')->lockForUpdate()->findOrFail($customer->id);
            $lawyer = LawyerProfile::query()->with(['user', 'location'])->lockForUpdate()->findOrFail($lawyer->id);
            $this->assertCustomerBookable($customer);
            $this->assertLawyerBookable($lawyer);

            $slot = AvailabilitySlot::query()
                ->whereKey($data['availability_slot_id'])
                ->where('lawyer_profile_id', $lawyer->id)
                ->lockForUpdate()
                ->first();

            if (!$slot || $slot->status !== 'available') {
                throw ValidationException::withMessages([
                    'availability_slot_id' => 'Khung giờ không còn khả dụng. Vui lòng chọn khung giờ khác.',
                ]);
            }

            $appointmentAt = $slot->available_date->copy()->setTimeFromTimeString($slot->start_time);
            if ($appointmentAt->isPast()) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Khung giờ đã hết hạn.']);
            }

            if (($data['consultation_type'] ?? null) === 'offline' && empty($data['location'])) {
                throw ValidationException::withMessages(['location' => 'Vui lòng cung cấp địa điểm khi chọn tư vấn trực tiếp.']);
            }
            if (($data['consultation_type'] ?? null) === 'online') {
                $data['location'] = null;
            }

            if (!empty($data['specialization_id'])) {
                $validSpecialization = $lawyer->specializations()
                    ->where('specializations.id', $data['specialization_id'])
                    ->where('specializations.is_active', true)
                    ->exists();
                if (!$validSpecialization) {
                    throw ValidationException::withMessages(['specialization_id' => 'Chuyên môn được chọn không thuộc luật sư này hoặc đã ngừng hoạt động.']);
                }
            }

            $hasConflict = $customer->appointments()
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->where('appointment_at', $appointmentAt)
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Bạn đã có một lịch hẹn khác vào thời gian này.']);
            }

            $appointment = Appointment::create([
                'customer_profile_id' => $customer->id,
                'lawyer_profile_id' => $lawyer->id,
                'specialization_id' => $data['specialization_id'] ?? null,
                'availability_slot_id' => $slot->id,
                'appointment_at' => $appointmentAt,
                'consultation_type' => $data['consultation_type'],
                'location' => $data['location'] ?? null,
                'problem_description' => $data['problem_description'],
                'attachment' => $attachment,
                'status' => 'pending',
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            $slot->update(['status' => 'booked']);

            return $appointment->fresh(['customer.user', 'lawyer.user', 'availabilitySlot']);
        });
    }

    public function transition(Appointment $appointment, string $to, ?string $note = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $to, $note) {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $current = $appointment->status;

            if (!array_key_exists($current, self::TRANSITIONS) || !in_array($to, self::TRANSITIONS[$current], true)) {
                throw ValidationException::withMessages(['status' => "Không thể chuyển lịch hẹn từ {$current} sang {$to}."]);
            }

            if (in_array($to, ['confirmed', 'rescheduled'], true) && $appointment->appointment_at->isPast()) {
                throw ValidationException::withMessages(['status' => 'Không thể xác nhận hoặc đổi sang một lịch hẹn đã bắt đầu hoặc đã quá giờ.']);
            }

            if ($to === 'completed' && $appointment->appointment_at->isFuture()) {
                throw ValidationException::withMessages(['status' => 'Chưa thể hoàn thành lịch hẹn vì thời gian tư vấn chưa bắt đầu.']);
            }

            if ($to === 'cancelled' && $appointment->appointment_at->isPast()) {
                throw ValidationException::withMessages(['status' => 'Không thể hủy lịch hẹn đã bắt đầu hoặc đã quá giờ.']);
            }

            $appointment->update([
                'status' => $to,
                'lawyer_note' => $note !== null ? $note : $appointment->lawyer_note,
            ]);

            if ($to === 'cancelled' && $appointment->availabilitySlot) {
                $slot = $appointment->availabilitySlot()->lockForUpdate()->first();
                if ($slot && $slot->status === 'booked') {
                    $slot->update(['status' => 'available']);
                }
            }

            return $appointment->fresh(['customer.user', 'lawyer.user', 'availabilitySlot']);
        });
    }

    public function reschedule(Appointment $appointment, AvailabilitySlot $newSlot, string $actor): Appointment
    {
        if (!in_array($actor, ['customer', 'lawyer'], true)) {
            throw ValidationException::withMessages(['appointment' => 'Đối tượng đổi lịch không hợp lệ.']);
        }

        return DB::transaction(function () use ($appointment, $newSlot) {
            $appointment = Appointment::query()->with(['lawyer.user', 'lawyer.location'])->lockForUpdate()->findOrFail($appointment->id);
            if (!in_array($appointment->status, self::ACTIVE_STATUSES, true)) {
                throw ValidationException::withMessages(['appointment' => 'Lịch hẹn hiện tại không thể đổi lịch.']);
            }

            $this->assertLawyerBookable($appointment->lawyer);

            if ($appointment->appointment_at->isPast()) {
                throw ValidationException::withMessages(['appointment' => 'Lịch hẹn đã bắt đầu hoặc đã quá giờ.']);
            }

            $newSlot = AvailabilitySlot::query()
                ->whereKey($newSlot->id)
                ->where('lawyer_profile_id', $appointment->lawyer_profile_id)
                ->lockForUpdate()
                ->first();

            if (!$newSlot || $newSlot->status !== 'available') {
                throw ValidationException::withMessages(['availability_slot_id' => 'Khung giờ mới không còn khả dụng.']);
            }

            $newAt = $newSlot->available_date->copy()->setTimeFromTimeString($newSlot->start_time);
            if ($newAt->isPast()) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Khung giờ mới đã hết hạn.']);
            }

            $oldSlot = $appointment->availabilitySlot()->lockForUpdate()->first();
            if ($oldSlot && $oldSlot->id === $newSlot->id) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Khung giờ mới phải khác khung giờ hiện tại.']);
            }

            $conflict = $appointment->customer()->first()?->appointments()
                ->where('id', '!=', $appointment->id)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->where('appointment_at', $newAt)
                ->exists();
            if ($conflict) {
                throw ValidationException::withMessages(['availability_slot_id' => 'Khách hàng đã có một lịch hẹn khác vào thời gian này.']);
            }

            $appointment->update([
                'availability_slot_id' => $newSlot->id,
                'appointment_at' => $newAt,
                'status' => 'rescheduled',
            ]);
            $newSlot->update(['status' => 'booked']);

            if ($oldSlot && $oldSlot->status === 'booked') {
                $oldSlot->update(['status' => 'available']);
            }

            return $appointment->fresh(['customer.user', 'lawyer.user', 'availabilitySlot']);
        });
    }

    private function assertCustomerBookable(CustomerProfile $customer): void
    {
        if (!$customer->user?->is_active || !$customer->user?->isCustomer()) {
            throw ValidationException::withMessages(['customer' => 'Tài khoản khách hàng không hợp lệ hoặc đang bị vô hiệu hóa.']);
        }
    }

    private function assertLawyerBookable(LawyerProfile $lawyer): void
    {
        if (!$lawyer->user?->is_active || !$lawyer->user?->isLawyer()) {
            throw ValidationException::withMessages(['lawyer' => 'Tài khoản luật sư không hợp lệ hoặc đang bị vô hiệu hóa.']);
        }
        if ($lawyer->verification_status !== 'verified') {
            throw ValidationException::withMessages(['lawyer' => 'Luật sư chưa được Super Admin xác minh.']);
        }
        if (!$lawyer->location || !$lawyer->location->is_active) {
            throw ValidationException::withMessages(['lawyer' => 'Địa điểm hoạt động của luật sư hiện không khả dụng.']);
        }
    }
}
