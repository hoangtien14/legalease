@extends('admin.layout')
@section('content')
<h2 class="mb-3">Thông báo hệ thống</h2>
<form method="POST" action="{{ route('admin.notifications.store') }}" class="card card-body mb-4">
@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Tiêu đề</label><input name="title" class="form-control" value="{{ old('title') }}" required></div>
<div class="col-md-3"><label class="form-label">Loại</label><select name="type" class="form-select"><option value="system">Hệ thống</option><option value="announcement">Thông báo</option><option value="legal_update">Cập nhật pháp luật</option></select></div>
<div class="col-md-3"><label class="form-label">Người nhận</label><select name="role_id" class="form-select"><option value="">Tất cả user hoạt động</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
<div class="col-12"><label class="form-label">Nội dung</label><textarea name="message" rows="4" class="form-control" required>{{ old('message') }}</textarea></div>
</div><button class="btn btn-primary mt-3">Gửi thông báo</button>
</form>
<div class="table-responsive bg-white"><table class="table table-hover"><thead><tr><th>Người nhận</th><th>Tiêu đề</th><th>Nội dung</th><th>Loại</th><th>Thời gian</th></tr></thead><tbody>@forelse($notifications as $n)<tr><td>{{ $n->user?->name }}<br><small>{{ $n->user?->email }}</small></td><td>{{ $n->title }}</td><td>{{ $n->message }}</td><td>{{ $n->type }}</td><td>{{ $n->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center py-4">Chưa có thông báo.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $notifications->links() }}</div>
@endsection
