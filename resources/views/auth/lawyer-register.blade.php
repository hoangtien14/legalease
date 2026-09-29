@extends('layouts.app')
@section('title','Đăng ký luật sư - LegalEase')
@section('content')
<div class="container py-5" style="max-width:950px">
    <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5">
        <h2>Đăng ký tài khoản luật sư</h2><p class="text-muted">Hồ sơ sẽ ở trạng thái <strong>Chờ duyệt</strong> cho đến khi Super Admin xác minh.</p>
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('lawyer.register.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Họ tên *</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                <div class="col-md-6"><label class="form-label">Số điện thoại *</label><input name="phone" class="form-control" value="{{ old('phone') }}" required></div>
                <div class="col-md-6"><label class="form-label">Số giấy phép *</label><input name="license_number" class="form-control" value="{{ old('license_number') }}" required></div>
                <div class="col-md-6"><label class="form-label">Mật khẩu *</label><input type="password" name="password" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Xác nhận mật khẩu *</label><input type="password" name="password_confirmation" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Tỉnh/Thành phố *</label><select name="location_id" class="form-select" required><option value="">-- Chọn --</option>@foreach($locations as $l)<option value="{{ $l->id }}" @selected(old('location_id')==$l->id)>{{ $l->province }}{{ $l->district?' - '.$l->district:'' }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Chức danh *</label><input name="title" class="form-control" value="{{ old('title') }}" required></div>
                <div class="col-12"><label class="form-label">Chuyên môn *</label><div class="row">@foreach($specializations as $s)<div class="col-md-4"><label class="form-check"><input class="form-check-input" type="checkbox" name="specializations[]" value="{{ $s->id }}" @checked(in_array($s->id, old('specializations',[])))><span class="form-check-label">{{ $s->name }}</span></label></div>@endforeach</div></div>
                <div class="col-12"><label class="form-label">Giới thiệu *</label><textarea name="bio" rows="4" class="form-control" required>{{ old('bio') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Học vấn *</label><textarea name="education" rows="3" class="form-control" required>{{ old('education') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Kinh nghiệm *</label><textarea name="experience" rows="3" class="form-control" required>{{ old('experience') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Số năm kinh nghiệm *</label><input type="number" name="experience_years" class="form-control" min="0" max="80" value="{{ old('experience_years') }}" required></div><div class="col-md-6"><label class="form-label">Phí tư vấn (VNĐ) *</label><input type="number" min="0" name="consultation_fee" class="form-control" value="{{ old('consultation_fee',0) }}" required></div>
                <div class="col-md-6"><label class="form-label">Ảnh đại diện</label><input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
            </div>
            <button class="btn btn-primary mt-4">Gửi hồ sơ đăng ký</button>
            <a href="{{ route('register') }}" class="btn btn-link mt-4">Đăng ký khách hàng</a>
        </form>
    </div></div>
</div>
@endsection
