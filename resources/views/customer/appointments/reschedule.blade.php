@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h2>Đổi lịch hẹn</h2>
    <p>Luật sư: <strong>{{ $appointment->lawyer->user->name }}</strong></p>
    <form method="POST" action="{{ route('customer.appointments.reschedule', $appointment) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Chọn khung giờ mới</label>
            <select name="availability_slot_id" class="form-select" required>
                <option value="">-- Chọn --</option>
                @foreach($slots as $slot)
                    <option value="{{ $slot->id }}">{{ $slot->available_date->format('d/m/Y') }} | {{ substr($slot->start_time,0,5) }} - {{ substr($slot->end_time,0,5) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Xác nhận đổi lịch</button>
        <a href="{{ route('customer.appointments.show',$appointment) }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
