@extends('admin.layout')
@section('content')
<h2 class="mb-3">Chỉnh sửa luật sư</h2>
<form method="POST" action="{{ route('admin.lawyers.update',$lawyer) }}" class="card card-body">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Họ tên</label><input name="name" class="form-control" value="{{ old('name',$lawyer->user?->name) }}" required></div>
<div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email',$lawyer->user?->email) }}" required></div>
<div class="col-md-6"><label class="form-label">Số điện thoại</label><input name="phone" class="form-control" value="{{ old('phone',$lawyer->user?->phone) }}"></div>
<div class="col-md-6"><label class="form-label">Địa điểm</label><select name="location_id" class="form-select"><option value="">-- Chưa chọn --</option>@foreach($locations as $x)<option value="{{ $x->id }}" @selected(old('location_id',$lawyer->location_id)==$x->id)>{{ $x->province }}{{ $x->district ? ' - '.$x->district : '' }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Số giấy phép</label><input name="license_number" class="form-control" value="{{ old('license_number',$lawyer->license_number) }}"></div>
<div class="col-md-6"><label class="form-label">Chức danh</label><input name="title" class="form-control" value="{{ old('title',$lawyer->title) }}"></div>
<div class="col-md-6"><label class="form-label">Số năm kinh nghiệm</label><input type="number" min="0" max="80" name="experience_years" class="form-control" value="{{ old('experience_years',$lawyer->experience_years) }}"></div>
<div class="col-12"><label class="form-label">Mô tả kinh nghiệm</label><textarea name="experience" class="form-control" rows="3">{{ old('experience',$lawyer->experience) }}</textarea></div>
<div class="col-md-6"><label class="form-label">Phí tư vấn (VNĐ)</label><input type="number" min="0" name="consultation_fee" class="form-control" value="{{ old('consultation_fee',$lawyer->consultation_fee) }}" required></div>
<div class="col-md-6"><label class="form-label">Trạng thái duyệt</label><select name="verification_status" class="form-select">@foreach(['pending'=>'Chờ duyệt','verified'=>'Đã duyệt','rejected'=>'Từ chối'] as $v=>$label)<option value="{{ $v }}" @selected(old('verification_status',$lawyer->verification_status)===$v)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Chuyên môn</label><select name="specialization_ids[]" class="form-select" multiple size="5">@foreach($specializations as $x)<option value="{{ $x->id }}" @selected(in_array($x->id,old('specialization_ids',$lawyer->specializations->pluck('id')->all())))>{{ $x->name }}</option>@endforeach</select><small class="text-muted">Giữ Ctrl để chọn nhiều.</small></div>
<div class="col-12"><label class="form-label">Học vấn</label><textarea name="education" class="form-control" rows="3">{{ old('education',$lawyer->education) }}</textarea>
</div>
<div class="col-12"><label class="form-label">Giới thiệu</label><textarea name="bio" class="form-control" rows="4">{{ old('bio',$lawyer->bio) }}</textarea></div>
<div class="col-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active',$lawyer->user?->is_active))><label class="form-check-label">Tài khoản đang hoạt động</label></div></div>
</div><div class="mt-3"><button class="btn btn-primary">Lưu thay đổi</button> <a class="btn btn-secondary" href="{{ route('admin.lawyers.show',$lawyer) }}">Hủy</a></div>
</form>
@endsection
