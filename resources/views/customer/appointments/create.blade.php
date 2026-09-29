@extends('layouts.app')
@section('title','Đặt lịch tư vấn - LegalEase')
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <a href="{{ route('lawyers.show', $lawyer->id) }}" class="text-decoration-none">← Quay lại hồ sơ luật sư</a>
                    <h2 class="mt-3">{{ $lawyer->user->name }}</h2>
                    <p class="text-muted">{{ $lawyer->title }}</p>
                    <hr>
                    <p class="mb-2"><strong>Phí tư vấn:</strong> {{ number_format((float)$lawyer->consultation_fee,0,',','.') }} đ</p>
                    <p class="mb-0"><strong>Chuyên môn:</strong> {{ $lawyer->specializations->pluck('name')->join(', ') }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="mb-4">Đặt lịch tư vấn</h2>
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif
                    @if($slots->isEmpty())
                        <div class="alert alert-warning">Luật sư hiện chưa có khung giờ trống.</div>
                    @else
                    <form action="{{ route('customer.appointments.store', $lawyer) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Khung giờ <span class="text-danger">*</span></label>
                            <select name="availability_slot_id" class="form-select" required>
                                <option value="">-- Chọn khung giờ --</option>
                                @foreach($slots as $slot)
                                    <option value="{{ $slot->id }}" @selected(old('availability_slot_id') == $slot->id)>
                                        {{ $slot->available_date->format('d/m/Y') }} — {{ substr($slot->start_time,0,5) }} đến {{ substr($slot->end_time,0,5) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lĩnh vực</label>
                            <select name="specialization_id" class="form-select">
                                <option value="">-- Chọn lĩnh vực --</option>
                                @foreach($specializations as $spec)
                                    <option value="{{ $spec->id }}" @selected(old('specialization_id') == $spec->id)>{{ $spec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình thức tư vấn <span class="text-danger">*</span></label>
                            <select name="consultation_type" id="consultation_type" class="form-select" required>
                                <option value="online" @selected(old('consultation_type','online') === 'online')>Online</option>
                                <option value="offline" @selected(old('consultation_type') === 'offline')>Trực tiếp</option>
                            </select>
                        </div>
                        <div class="mb-3" id="location-wrap" style="display:none">
                            <label class="form-label">Địa điểm</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Địa chỉ gặp trực tiếp">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vấn đề cần tư vấn <span class="text-danger">*</span></label>
                            <textarea name="problem_description" rows="5" class="form-control" required placeholder="Mô tả vấn đề bạn cần luật sư hỗ trợ...">{{ old('problem_description') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="customer_note" rows="3" class="form-control" placeholder="Thông tin bổ sung nếu có">{{ old('customer_note') }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tài liệu đính kèm (tối đa 5MB)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <button class="btn btn-primary w-100 btn-lg">Gửi yêu cầu đặt lịch</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const type = document.getElementById('consultation_type');
const wrap = document.getElementById('location-wrap');
function toggleLocation(){ wrap.style.display = type.value === 'offline' ? 'block' : 'none'; }
type?.addEventListener('change', toggleLocation); toggleLocation();
</script>
@endsection
