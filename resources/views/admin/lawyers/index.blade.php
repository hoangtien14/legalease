@extends('admin.layout')
@section('content')
<h2 class="mb-3">Quản lý luật sư</h2>
<form method="GET" class="card card-body mb-4">
    <div class="row g-2">
        <div class="col-md-3"><input class="form-control" name="keyword" value="{{ request('keyword') }}" placeholder="Tên hoặc email"></div>
        <div class="col-md-2"><select class="form-select" name="verification_status"><option value="">Tất cả duyệt</option>@foreach(['pending'=>'Chờ duyệt','verified'=>'Đã duyệt','rejected'=>'Từ chối'] as $v=>$label)<option value="{{ $v }}" @selected(request('verification_status')===$v)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="location_id"><option value="">Tất cả địa điểm</option>@foreach($locations as $x)<option value="{{ $x->id }}" @selected(request('location_id')==$x->id)>{{ $x->province }}{{ $x->district ? ' - '.$x->district : '' }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="specialization_id"><option value="">Tất cả chuyên môn</option>@foreach($specializations as $x)<option value="{{ $x->id }}" @selected(request('specialization_id')==$x->id)>{{ $x->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="is_active"><option value="">Tất cả tài khoản</option><option value="1" @selected(request('is_active')==='1')>Đang hoạt động</option><option value="0" @selected(request('is_active')==='0')>Đã khóa</option></select></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Lọc</button></div>
    </div>
</form>
<div class="bg-white table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Luật sư</th><th>Địa điểm</th><th>Chuyên môn</th><th>Rating</th><th>Duyệt</th><th>Tài khoản</th><th>Thao tác</th></tr></thead><tbody>
@forelse($lawyers as $l)<tr>
<td><strong>{{ $l->user?->name }}</strong><br><small>{{ $l->user?->email }}</small><br><small>{{ $l->license_number }}</small></td>
<td>{{ $l->location?->province }}</td><td>{{ $l->specializations->pluck('name')->join(', ') }}</td><td>⭐ {{ number_format((float)($l->reviews_avg_rating ?? 0),1) }}</td>
<td><span class="badge text-bg-{{ $l->verification_status==='verified'?'success':($l->verification_status==='pending'?'warning':'danger') }}">{{ $l->verification_status }}</span></td>
<td><span class="badge text-bg-{{ $l->user?->is_active ? 'success':'secondary' }}">{{ $l->user?->is_active ? 'active':'inactive' }}</span></td>
<td class="text-nowrap"><a class="btn btn-sm btn-info" href="{{ route('admin.lawyers.show',$l) }}">Xem</a>
<form class="d-inline" method="POST" action="{{ route('admin.lawyers.verify',$l) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="verified"><button class="btn btn-sm btn-success">Duyệt</button></form>
<form class="d-inline" method="POST" action="{{ route('admin.lawyers.verify',$l) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-warning">Từ chối</button></form>
<form class="d-inline" method="POST" action="{{ route('admin.lawyers.toggle',$l) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-secondary">{{ $l->user?->is_active ? 'Khóa':'Mở' }}</button></form>
<form class="d-inline" method="POST" action="{{ route('admin.lawyers.destroy',$l) }}" onsubmit="return confirm('Xóa luật sư này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Xóa</button></form></td>
</tr>@empty<tr><td colspan="7" class="text-center py-4">Không có luật sư phù hợp.</td></tr>@endforelse</tbody></table></div>
<div class="mt-3">{{ $lawyers->links() }}</div>
@endsection
