<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;

class ReviewController extends Controller
{
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer && $appointment->customer_profile_id === $customer->id, 403);

        if ($appointment->status !== 'completed') {
            return back()->withErrors(['review' => 'Chỉ có thể đánh giá sau khi lịch hẹn hoàn thành.']);
        }

        if ($appointment->review()->exists()) {
            return back()->withErrors(['review' => 'Lịch hẹn này đã được đánh giá.']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($appointment, $customer, $data) {
                $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
                if ($appointment->status !== 'completed' || $appointment->customer_profile_id !== $customer->id || $appointment->review()->exists()) {
                    throw ValidationException::withMessages(['review' => 'Lịch hẹn không còn đủ điều kiện để đánh giá.']);
                }
                Review::create([
                    'appointment_id' => $appointment->id,
                    'customer_profile_id' => $customer->id,
                    'lawyer_profile_id' => $appointment->lawyer_profile_id,
                    'rating' => $data['rating'],
                    'comment' => $data['comment'] ?? null,
                    'is_visible' => true,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        NotificationService::send($appointment->lawyer->user, 'Khách hàng đã đánh giá', 'Bạn vừa nhận được một đánh giá mới từ khách hàng.', 'review', route('lawyer.dashboard'));

        return back()->with('success', 'Cảm ơn bạn đã đánh giá luật sư.');
    }
}
