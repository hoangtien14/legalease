@extends('layouts.app')
@section('title','Hồ sơ - LegalEase')
@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="mb-1">Quản lý hồ sơ</h2>
                    <p class="text-muted">Cập nhật thông tin tài khoản và hồ sơ chuyên môn.</p>
                    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Họ và tên</label><input name="name" class="form-control" value="{{ old('name',$user->name) }}" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="{{ $user->email }}" disabled></div>
                            <div class="col-md-6"><label class="form-label">Số điện thoại</label><input name="phone" class="form-control" value="{{ old('phone',$user->phone) }}"></div>
                            <div class="col-md-6"><label class="form-label">Tỉnh/Thành phố</label><select name="location_id" class="form-select"><option value="">-- Chọn --</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id', optional($user->customerProfile ?? $user->lawyerProfile)->location_id)==$location->id)>{{ $location->province }}{{ $location->district ? ' - '.$location->district : '' }}</option>@endforeach</select></div>
                            <div class="col-12"><label class="form-label">Địa chỉ</label><input name="address" class="form-control" value="{{ old('address', optional($user->customerProfile)->address) }}"></div>
                            @if($user->isCustomer())
                                <div class="col-md-6"><label class="form-label">Ngày sinh</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($user->customerProfile?->date_of_birth)->format('Y-m-d')) }}"></div>
                                <div class="col-md-6"><label class="form-label">Giới tính</label><select name="gender" class="form-select"><option value="">-- Chọn --</option><option value="male" @selected(old('gender',$user->customerProfile?->gender)==='male')>Nam</option><option value="female" @selected(old('gender',$user->customerProfile?->gender)==='female')>Nữ</option><option value="other" @selected(old('gender',$user->customerProfile?->gender)==='other')>Khác</option></select></div>
                            @endif
                            @if($user->isLawyer())
                                <div class="col-md-6"><label class="form-label">Số giấy phép hành nghề</label><input name="license_number" class="form-control" value="{{ old('license_number',$user->lawyerProfile?->license_number) }}"></div>
                                <div class="col-md-6"><label class="form-label">Chức danh</label><input name="title" class="form-control" value="{{ old('title',$user->lawyerProfile?->title) }}"></div>
                                <div class="col-12"><label class="form-label">Giới thiệu</label><textarea name="bio" class="form-control" rows="4">{{ old('bio',$user->lawyerProfile?->bio) }}</textarea></div>
                                <div class="col-md-6"><label class="form-label">Học vấn</label><textarea name="education" class="form-control" rows="3">{{ old('education',$user->lawyerProfile?->education) }}</textarea></div>
                                <div class="col-md-6"><label class="form-label">Kinh nghiệm</label><textarea name="experience" class="form-control" rows="3">{{ old('experience',$user->lawyerProfile?->experience) }}</textarea></div>
                                <div class="col-12"><label class="form-label">Chuyên ngành</label><input type="hidden" name="specialization_ids_present" value="1"><div class="row g-2">@foreach($specializations as $specialization)<div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="specialization_ids[]" value="{{ $specialization->id }}" @checked($user->lawyerProfile?->specializations?->contains($specialization->id))><span class="form-check-label">{{ $specialization->name }}</span></label></div>@endforeach</div></div>
                                <div class="col-md-6"><label class="form-label">Số năm kinh nghiệm</label><input type="number" name="experience_years" class="form-control" min="0" max="80" value="{{ old('experience_years',$user->lawyerProfile?->experience_years) }}"></div>
                                <div class="col-md-6"><label class="form-label">Phí tư vấn (VNĐ)</label><input type="number" name="consultation_fee" class="form-control" min="0" value="{{ old('consultation_fee',$user->lawyerProfile?->consultation_fee) }}"></div>
                            @endif
                            <div class="col-12"><label class="form-label">Ảnh đại diện</label><input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG, WEBP — tối đa 2MB.</div></div>
                        </div>
                        <button class="btn btn-primary mt-4">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0"><div class="card-body p-4">
                <h4>Đổi mật khẩu</h4>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Mật khẩu mới</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Xác nhận mật khẩu</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    <button class="btn btn-outline-primary w-100">Đổi mật khẩu</button>
                </form>
            </div></div>
        </div>
    </div>
</div>
@endsection
