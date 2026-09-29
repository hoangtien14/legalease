<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LawyerProfile;
use App\Models\AvailabilitySlot;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class LawyerDashboardController extends Controller
{
    public function __construct(private AppointmentService $appointmentService) {}

    public function index(Request $request): View
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer, 403);
        $appointments = $lawyer->appointments()
            ->with(['customer.user', 'specialization', 'availabilitySlot'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('appointment_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('appointment_at', '<=', $request->date('to')))
            ->latest('appointment_at')->paginate(10)->withQueryString();
        $stats = [
            'pending' => $lawyer->appointments()->where('status', 'pending')->count(),
            'confirmed' => $lawyer->appointments()->whereIn('status', ['confirmed', 'rescheduled'])->count(),
            'completed' => $lawyer->appointments()->where('status', 'completed')->count(),
            'available_slots' => $lawyer->availabilitySlots()->where('status', 'available')->whereDate('available_date', '>=', now()->toDateString())->count(),
        ];
        return view('lawyer.dashboard', compact('lawyer', 'appointments', 'stats'));
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $appointment->lawyer_profile_id === $lawyer->id, 403);
        $data = $request->validate([
            'status' => ['required', 'in:confirmed,completed,cancelled'],
            'lawyer_note' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $appointment = $this->appointmentService->transition($appointment, $data['status'], $data['lawyer_note'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $messages = [
            'confirmed' => ['Lịch hẹn được xác nhận', 'Luật sư đã xác nhận lịch hẹn của bạn.', 'appointment'],
            'completed' => ['Lịch tư vấn đã hoàn thành', 'Lịch tư vấn của bạn đã được đánh dấu hoàn thành.', 'appointment'],
            'cancelled' => ['Lịch hẹn bị hủy', 'Luật sư đã hủy lịch hẹn.', 'cancel'],
        ];
        [$title, $message, $type] = $messages[$data['status']];
        NotificationService::send($appointment->customer->user, $title, $message, $type, route('customer.appointments.show', $appointment));
        return back()->with('success', 'Đã cập nhật lịch hẹn.');
    }

    public function rescheduleAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $appointment->lawyer_profile_id === $lawyer->id, 403);
        $data = $request->validate(['availability_slot_id' => ['required', 'integer', 'exists:availability_slots,id']]);
        try {
            $appointment = $this->appointmentService->reschedule($appointment, AvailabilitySlot::findOrFail($data['availability_slot_id']), 'lawyer');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        NotificationService::send($appointment->customer->user, 'Luật sư đổi lịch hẹn', 'Luật sư đã chuyển lịch tư vấn của bạn sang khung giờ mới.', 'appointment', route('customer.appointments.show', $appointment));
        return back()->with('success', 'Đã đổi lịch và thông báo cho khách hàng.');
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $lawyer->verification_status === 'verified' && auth()->user()->is_active, 403);
        $data = $request->validate([
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if ($data['available_date'] === now()->toDateString() && $data['start_time'] <= now()->format('H:i')) {
            return back()->withErrors(['start_time' => 'Không thể tạo khung giờ đã bắt đầu hoặc đã qua.'])->withInput();
        }

        DB::transaction(function () use ($lawyer, $data) {
            LawyerProfile::query()->lockForUpdate()->findOrFail($lawyer->id);
            $overlap = $lawyer->availabilitySlots()->whereDate('available_date', $data['available_date'])
                ->whereIn('status', ['available', 'booked', 'blocked'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_time' => 'Khung giờ bị trùng với lịch đã có.']);
            }
            $lawyer->availabilitySlots()->create([
                'available_date' => $data['available_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'status' => 'available',
            ]);
        });
        return back()->with('success', 'Đã thêm khung giờ.');
    }

    public function updateSlot(Request $request, AvailabilitySlot $slot): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $slot->lawyer_profile_id === $lawyer->id, 403);
        abort_unless($lawyer->verification_status === 'verified' && auth()->user()->is_active, 403, 'Tài khoản luật sư chưa đủ điều kiện quản lý lịch rảnh.');
        abort_if($slot->status === 'booked', 422, 'Không thể sửa khung giờ đã được đặt.');

        $data = $request->validate([
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);
        if ($data['available_date'] === now()->toDateString() && $data['start_time'] <= now()->format('H:i')) {
            return back()->withErrors(['start_time' => 'Không thể chuyển khung giờ đến thời điểm đã bắt đầu hoặc đã qua.'])->withInput();
        }

        DB::transaction(function () use ($lawyer, $slot, $data) {
            LawyerProfile::query()->lockForUpdate()->findOrFail($lawyer->id);
            $slot = AvailabilitySlot::query()->lockForUpdate()->findOrFail($slot->id);
            if ($slot->status === 'booked') {
                throw ValidationException::withMessages(['slot' => 'Khung giờ vừa được đặt, không thể sửa.']);
            }
            $overlap = $lawyer->availabilitySlots()
                ->where('id', '!=', $slot->id)
                ->whereDate('available_date', $data['available_date'])
                ->whereIn('status', ['available', 'booked', 'blocked'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();
            if ($overlap) {
                throw ValidationException::withMessages(['start_time' => 'Khung giờ bị trùng với lịch đã có.']);
            }
            $slot->update($data);
        });
        return back()->with('success', 'Đã cập nhật khung giờ.');
    }

    public function toggleSlot(AvailabilitySlot $slot): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $slot->lawyer_profile_id === $lawyer->id, 403);
        abort_unless($lawyer->verification_status === 'verified' && auth()->user()->is_active, 403, 'Tài khoản luật sư chưa đủ điều kiện quản lý lịch rảnh.');
        if ($slot->status === 'booked') {
            return back()->withErrors(['slot' => 'Không thể khóa khung giờ đã được đặt.']);
        }
        $slot->update(['status' => $slot->status === 'blocked' ? 'available' : 'blocked']);
        return back()->with('success', $slot->status === 'blocked' ? 'Đã khóa khung giờ.' : 'Đã mở lại khung giờ.');
    }

    public function deleteSlot(AvailabilitySlot $slot): RedirectResponse
    {
        $lawyer = auth()->user()->lawyerProfile;
        abort_unless($lawyer && $slot->lawyer_profile_id === $lawyer->id, 403);
        abort_unless($lawyer->verification_status === 'verified' && auth()->user()->is_active, 403, 'Tài khoản luật sư chưa đủ điều kiện quản lý lịch rảnh.');
        if ($slot->status === 'booked') return back()->withErrors(['slot' => 'Không thể xóa khung giờ đã được đặt.']);
        $slot->delete();
        return back()->with('success', 'Đã xóa khung giờ.');
    }
}
