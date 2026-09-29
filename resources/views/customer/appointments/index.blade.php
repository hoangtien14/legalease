@extends('layouts.app')
@section('title','Lịch tư vấn của tôi - LegalEase')
@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="mb-1">Lịch tư vấn của tôi</h1><p class="text-muted mb-0">Theo dõi các lịch hẹn với luật sư.</p></div>
        <a href="{{ route('lawyers.index') }}" class="btn btn-primary">Tìm luật sư</a>
    </div>

    <form method="GET" class="card card-body border-0 shadow-sm mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="">Tất cả</option>@foreach(['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','rescheduled'=>'Đã đổi lịch','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Từ ngày</label><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
            <div class="col-md-3"><label class="form-label">Đến ngày</label><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary">Lọc</button><a href="{{ route('customer.appointments.index') }}" class="btn btn-outline-secondary">Xóa</a></div>
        </div>
    </form>

    @if($appointments->count())
        <div class="row g-3">
            @foreach($appointments as $appointment)
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-md-3">
                                    <div class="small text-muted">Luật sư</div>
                                    <strong>{{ $appointment->lawyer->user->name ?? 'N/A' }}</strong>
                                    <div class="small">{{ $appointment->specialization->name ?? 'Tư vấn pháp luật' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted">Thời gian</div>
                                    <strong>{{ $appointment->appointment_at?->format('d/m/Y H:i') }}</strong>
                                </div>
                                <div class="col-md-2">
                                    <div class="small text-muted">Hình thức</div>
                                    {{ $appointment->consultation_type === 'online' ? 'Online' : 'Trực tiếp' }}
                                </div>
                                <div class="col-md-2">
                                    <span class="badge text-bg-{{ match($appointment->status) {
                                        'pending'=>'warning','confirmed'=>'success','completed'=>'primary','cancelled'=>'secondary',default=>'info'
                                    } }}">{{ ucfirst($appointment->status) }}</span>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <a href="{{ route('customer.appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">Chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $appointments->links() }}</div>
    @else
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
            <h4>Chưa có lịch tư vấn</h4><p class="text-muted">Hãy tìm một luật sư phù hợp để bắt đầu.</p>
            <a href="{{ route('lawyers.index') }}" class="btn btn-primary">Tìm luật sư</a>
        </div></div>
    @endif
</div>
@endsection
