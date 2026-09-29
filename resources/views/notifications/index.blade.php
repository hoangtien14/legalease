@extends('layouts.app')
@section('title','Thông báo - LegalEase')
@section('content')
<div class="container py-5"><div class="card border-0 shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-4"><h2 class="mb-0">Thông báo</h2><form method="POST" action="{{ route('notifications.readAll') }}">@csrf @method('PATCH')<button class="btn btn-outline-primary btn-sm">Đánh dấu tất cả đã đọc</button></form></div>
@forelse($notifications as $notification)
<div class="border rounded p-3 mb-3 {{ $notification->read_at ? '' : 'bg-light' }}"><div class="d-flex justify-content-between gap-3"><div><strong>{{ $notification->title }}</strong><p class="mb-1 mt-1">{{ $notification->message }}</p><small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small></div>@if(!$notification->read_at)<form method="POST" action="{{ route('notifications.read',$notification) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Đã đọc</button></form>@endif</div></div>
@empty<p class="text-muted">Bạn chưa có thông báo.</p>@endforelse
{{ $notifications->links() }}</div></div></div>
@endsection
