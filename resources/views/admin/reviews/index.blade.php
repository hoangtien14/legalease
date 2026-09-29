@extends('admin.layout')
@section('title','Quản lý đánh giá - LegalEase')
@section('content')
<div class="content-area">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-1">Quản lý đánh giá</h2><p class="text-muted mb-0">Kiểm duyệt đánh giá của khách hàng trước khi hiển thị công khai.</p></div>
    </div>
    <form class="card card-body mb-4" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Số sao</label><select name="rating" class="form-select"><option value="">Tất cả</option>@for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected(request('rating')==$i)>{{ $i }} sao</option>@endfor</select></div>
            <div class="col-md-4"><label class="form-label">Hiển thị</label><select name="visibility" class="form-select"><option value="">Tất cả</option><option value="1" @selected(request('visibility')==='1')>Đang hiển thị</option><option value="0" @selected(request('visibility')==='0')>Đang ẩn</option></select></div>
            <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary">Lọc</button><a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">Xóa lọc</a></div>
        </div>
    </form>
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Khách hàng</th><th>Luật sư</th><th>Đánh giá</th><th>Bình luận</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    @forelse($reviews as $review)
    <tr><td>{{ $review->customer?->user?->name }}</td><td>{{ $review->lawyer?->user?->name }}</td><td><span class="rating-stars">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</span></td><td style="max-width:360px">{{ $review->comment ?: '—' }}</td><td><span class="badge text-bg-{{ $review->is_visible ? 'success':'secondary' }}">{{ $review->is_visible ? 'Hiển thị':'Đang ẩn' }}</span></td><td><form method="POST" action="{{ route('admin.reviews.toggle',$review) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">{{ $review->is_visible ? 'Ẩn':'Hiện' }}</button></form></td></tr>
    @empty <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có đánh giá.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $reviews->links() }}</div></div>
</div>
@endsection
