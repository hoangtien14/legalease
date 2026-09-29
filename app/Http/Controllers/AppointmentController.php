<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AvailabilitySlot;
use App\Models\LawyerProfile;
use App\Models\Specialization;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService) {}

    public function index(Request $request): View
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,rescheduled,completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $customer = auth()->user()->customerProfile;
        $appointments = $customer
            ? $customer->appointments()
                ->with(['lawyer.user', 'specialization'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->when($request->filled('from'), fn ($q) => $q->whereDate('appointment_at', '>=', $request->date('from')))
                ->when($request->filled('to'), fn ($q) => $q->whereDate('appointment_at', '<=', $request->date('to')))
                ->latest('appointment_at')->paginate(10)->withQueryString()
            : collect();
        return view('customer.appointments.index', compact('appointments'));
    }

    public function create(LawyerProfile $lawyer): View
    {
        abort_unless($lawyer->verification_status === 'verified' && $lawyer->user?->is_active, 404);
        $lawyer->load(['user', 'specializations']);
        $slots = $lawyer->availabilitySlots()->where('status', 'available')
            ->whereDate('available_date', '>=', now()->toDateString())
            ->orderBy('available_date')->orderBy('start_time')->get();
        $specializations = $lawyer->specializations()->where('is_active', true)->orderBy('name')->get();
        return view('customer.appointments.create', compact('lawyer', 'slots', 'specializations'));
    }

    public function store(Request $request, LawyerProfile $lawyer): RedirectResponse
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer, 403, 'Tài khoản chưa có hồ sơ khách hàng.');

        $data = $request->validate([
            'availability_slot_id' => ['required', 'integer', 'exists:availability_slots,id'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'consultation_type' => ['required', 'in:online,offline'],
            'location' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['required', 'string', 'min:10', 'max:5000'],
            'customer_note' => ['nullable', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        if ($data['consultation_type'] === 'offline' && empty($data['location'])) {
            throw ValidationException::withMessages(['location' => 'Vui lòng nhập địa điểm khi chọn tư vấn trực tiếp.']);
        }
        if ($data['consultation_type'] === 'online') {
            $data['location'] = null;
        }

        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('appointments', 'public')
            : null;

        try {
            $appointment = $this->appointmentService->book($customer, $lawyer, $data, $attachment);
        } catch (\Throwable $e) {
            if ($attachment) Storage::disk('public')->delete($attachment);
            throw $e;
        }

        NotificationService::send($lawyer->user, 'Có yêu cầu tư vấn mới', 'Bạn vừa nhận một yêu cầu tư vấn mới từ ' . auth()->user()->name . '.', 'appointment', route('lawyer.dashboard'));
        NotificationService::send(auth()->user(), 'Đặt lịch thành công', 'Yêu cầu tư vấn đang chờ luật sư xác nhận.', 'appointment', route('customer.appointments.show', $appointment));

        return redirect()->route('customer.appointments.show', $appointment)->with('success', 'Đặt lịch thành công. Vui lòng chờ luật sư xác nhận.');
    }

    public function show(Appointment $appointment): View
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer && $appointment->customer_profile_id === $customer->id, 403);
        $appointment->load(['lawyer.user', 'lawyer.location', 'specialization', 'availabilitySlot', 'review']);
        return view('customer.appointments.show', compact('appointment'));
    }

    public function rescheduleForm(Appointment $appointment): View
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer && $appointment->customer_profile_id === $customer->id, 403);
        abort_unless(in_array($appointment->status, ['pending', 'confirmed', 'rescheduled'], true), 422);
        $slots = $appointment->lawyer->availabilitySlots()->where('status', 'available')
            ->whereDate('available_date', '>=', now()->toDateString())
            ->orderBy('available_date')->orderBy('start_time')->get();
        return view('customer.appointments.reschedule', compact('appointment', 'slots'));
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer && $appointment->customer_profile_id === $customer->id, 403);
        $data = $request->validate(['availability_slot_id' => ['required', 'integer', 'exists:availability_slots,id']]);
        $newSlot = AvailabilitySlot::findOrFail($data['availability_slot_id']);
        $appointment = $this->appointmentService->reschedule($appointment, $newSlot, 'customer');
        NotificationService::send($appointment->lawyer->user, 'Lịch hẹn được đổi lịch', 'Khách hàng đã chuyển lịch tư vấn sang khung giờ mới.', 'appointment', route('lawyer.dashboard'));
        NotificationService::send(auth()->user(), 'Đổi lịch thành công', 'Lịch hẹn đã được chuyển sang thời gian mới.', 'appointment', route('customer.appointments.show', $appointment));
        return redirect()->route('customer.appointments.show', $appointment)->with('success', 'Đổi lịch thành công.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer && $appointment->customer_profile_id === $customer->id, 403);
        try {
            $appointment = $this->appointmentService->transition($appointment, 'cancelled');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        NotificationService::send($appointment->lawyer->user, 'Lịch hẹn bị hủy', 'Khách hàng đã hủy lịch hẹn.', 'cancel', route('lawyer.dashboard'));
        return redirect()->route('customer.appointments.index')->with('success', 'Đã hủy lịch hẹn.');
    }
}
