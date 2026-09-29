<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService) {}

    public function index(Request $request)
    {
        $appointments = Appointment::with(['customer.user','lawyer.user','specialization','availabilitySlot'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $keyword = trim($request->string('keyword')->toString());
                $q->where(function ($x) use ($keyword) {
                    $x->whereHas('customer.user', fn ($u) => $u->where('name', 'like', "%{$keyword}%"))
                      ->orWhereHas('lawyer.user', fn ($u) => $u->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->latest('appointment_at')->paginate(15)->withQueryString();
        return view('admin.appointments.index', compact('appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate(['status' => ['required','in:confirmed,completed,cancelled']]);
        try {
            $appointment = $this->appointmentService->transition($appointment, $data['status']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $messages = [
            'confirmed' => ['Lịch hẹn được xác nhận','Quản trị viên đã xác nhận lịch hẹn của bạn.','appointment'],
            'completed' => ['Lịch tư vấn đã hoàn thành','Lịch tư vấn đã được đánh dấu hoàn thành.','appointment'],
            'cancelled' => ['Lịch hẹn bị hủy','Lịch hẹn đã bị hủy bởi quản trị viên.','cancel'],
        ];
        [$title,$message,$type] = $messages[$data['status']];
        NotificationService::send($appointment->customer->user,$title,$message,$type,route('customer.appointments.show',$appointment));
        if ($appointment->lawyer?->user) {
            NotificationService::send($appointment->lawyer->user, 'Quản trị viên cập nhật lịch hẹn', 'Trạng thái lịch hẹn đã được cập nhật thành: ' . $data['status'] . '.', $type, route('lawyer.dashboard'));
        }
        return back()->with('success','Đã cập nhật trạng thái lịch hẹn.');
    }

    public function destroy(Appointment $appointment)
    {
        // Keep business history: completed/cancelled appointments should not be physically deleted from the admin UI.
        if (in_array($appointment->status, ['completed','cancelled'], true)) {
            return back()->withErrors(['appointment' => 'Không xóa lịch hẹn đã kết thúc hoặc đã hủy; hãy giữ lịch sử để đối soát.']);
        }
        return back()->withErrors(['appointment' => 'Chỉ nên hủy lịch hẹn thông qua nghiệp vụ, không xóa trực tiếp.']);
    }
}
