@extends('layouts.app')
@section('title','Chi tiết lịch hẹn - LegalEase')
@section('content')
<div class="container py-5">
    <a href="{{ route('customer.appointments.index') }}" class="text-decoration-none">← Lịch của tôi</a>
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div><h2>Chi tiết lịch tư vấn</h2><p class="text-muted mb-0">Mã lịch #{{ $appointment->id }}</p></div>
                        <span class="badge text-bg-{{ match($appointment->status) {'pending'=>'warning','confirmed'=>'success','completed'=>'primary','cancelled'=>'secondary',default=>'info'} }}">{{ ucfirst($appointment->status) }}</span>
                    </div>
                    <hr>
                    <dl class="row">
                        <dt class="col-sm-4">Luật sư</dt><dd class="col-sm-8">{{ $appointment->lawyer->user->name ?? 'N/A' }}</dd>
                        <dt class="col-sm-4">Thời gian</dt><dd class="col-sm-8">{{ $appointment->appointment_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Lĩnh vực</dt><dd class="col-sm-8">{{ $appointment->specialization->name ?? 'Chưa chọn' }}</dd>
                        <dt class="col-sm-4">Hình thức</dt><dd class="col-sm-8">{{ $appointment->consultation_type === 'online' ? 'Online' : 'Trực tiếp' }}</dd>
                        @if($appointment->location)<dt class="col-sm-4">Địa điểm</dt><dd class="col-sm-8">{{ $appointment->location }}</dd>@endif
                    </dl>
                    <h5>Vấn đề cần tư vấn</h5>
                    <p class="bg-light rounded p-3">{{ $appointment->problem_description }}</p>
                    @if($appointment->customer_note)<h5>Ghi chú</h5><p>{{ $appointment->customer_note }}</p>@endif
                    @if($appointment->lawyer_note)<h5>Ghi chú của luật sư</h5><p class="bg-light rounded p-3">{{ $appointment->lawyer_note }}</p>@endif

                    @if($appointment->status === 'completed' && !$appointment->review)
                        <hr class="my-4">
                        <h5>Đánh giá luật sư</h5>
                        <form method="POST" action="{{ route('customer.appointments.review',$appointment) }}">
                            @csrf
                            <div class="mb-3"><label class="form-label d-block">Mức đánh giá</label><div class="rating-stars" role="radiogroup" aria-label="Chọn số sao">@for($i=5;$i>=1;$i--)<input type="radio" id="rating-{{ $i }}" name="rating" value="{{ $i }}" required><label for="rating-{{ $i }}" title="{{ $i }} sao"><i class="fa-solid fa-star"></i></label>@endfor</div><div class="small text-muted mt-1">Chọn từ 1 đến 5 sao</div></div>
                            <div class="mb-3"><label class="form-label">Nhận xét</label><textarea name="comment" class="form-control" rows="3" maxlength="2000"></textarea></div>
                            <button class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    @elseif($appointment->review)
                        <hr class="my-4"><h5>Đánh giá của bạn</h5><p class="mb-1">⭐ {{ $appointment->review->rating }}/5</p><p class="text-muted">{{ $appointment->review->comment }}</p>
                    @endif

                    @if(in_array($appointment->status,['pending','confirmed','rescheduled']) && $appointment->appointment_at?->isFuture())
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('customer.appointments.reschedule.form',$appointment) }}" class="btn btn-outline-primary">Đổi lịch</a>
                            <form action="{{ route('customer.appointments.cancel',$appointment) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy lịch này?')">
                                @csrf
                                <button class="btn btn-outline-danger">Hủy lịch hẹn</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0"><div class="card-body p-4">
                <h5>Luật sư</h5>
                <h4>{{ $appointment->lawyer->user->name ?? 'N/A' }}</h4>
                <p class="text-muted">{{ $appointment->lawyer->title ?? '' }}</p>
                <a href="{{ route('lawyers.show',$appointment->lawyer->id) }}" class="btn btn-outline-primary w-100">Xem hồ sơ</a>
            </div></div>
        </div>
    </div>
</div>
@endsection
