@extends('layouts.app')
@section('title','Bảng điều khiển khách hàng - LegalEase')
@section('content')
<div class="dashboard-wrapper">
  <aside class="sidebar"><div class="brand"><i class="fa-solid fa-scale-balanced me-2"></i>LegalEase</div><ul class="nav flex-column mt-2">
    <li class="nav-item"><a class="nav-link active" href="{{ route('customer.dashboard') }}"><i class="fa-solid fa-gauge"></i>Bảng điều khiển</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('lawyers.index') }}"><i class="fa-solid fa-magnifying-glass"></i>Tìm luật sư</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('customer.appointments.index') }}"><i class="fa-solid fa-calendar-check"></i>Lịch hẹn của tôi</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}"><i class="fa-solid fa-user"></i>Hồ sơ</a></li>
    <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link border-0 bg-transparent w-100 text-start"><i class="fa-solid fa-right-from-bracket"></i>Đăng xuất</button></form></li>
  </ul></aside>
  <main class="main-content"><div class="topbar"><h5 class="mb-0">Bảng điều khiển khách hàng</h5><span class="text-muted">Xin chào, <strong>{{ auth()->user()->name }}</strong></span></div>
  <div class="content-area"><div class="row g-3 mb-4">
    @foreach([['upcoming','Sắp tới','fa-clock'],['total','Tổng lịch hẹn','fa-list'],['confirmed','Đã xác nhận','fa-check'],['cancelled','Đã hủy','fa-xmark']] as [$key,$label,$icon])<div class="col-sm-6 col-lg-3"><div class="stat-card d-flex justify-content-between align-items-center"><div><div class="stat-value">{{ $stats[$key] }}</div><div class="text-muted small">{{ $label }}</div></div><div class="stat-icon"><i class="fa-solid {{ $icon }}"></i></div></div></div>@endforeach
  </div>
  <div class="card mb-4"><div class="card-header bg-white"><strong>Lịch hẹn sắp tới</strong></div><div class="table-responsive"><table class="table mb-0 align-middle"><thead class="table-light"><tr><th>Luật sư</th><th>Chuyên ngành</th><th>Ngày</th><th>Giờ</th><th>Trạng thái</th><th>Hành động</th></tr></thead><tbody>
  @forelse($appointments as $appointment)<tr><td><strong>{{ $appointment->lawyer->user->name ?? 'N/A' }}</strong></td><td>{{ $appointment->specialization->name ?? 'Tư vấn pháp luật' }}</td><td>{{ $appointment->appointment_at?->format('d/m/Y') }}</td><td>{{ $appointment->appointment_at?->format('H:i') }}</td><td><span class="badge text-bg-{{ match($appointment->status){'pending'=>'warning','confirmed'=>'success','rescheduled'=>'info','completed'=>'primary',default=>'secondary'} }}">{{ ucfirst($appointment->status) }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('customer.appointments.show',$appointment) }}">Chi tiết</a></td></tr>@empty<tr><td colspan="6" class="text-center py-4 text-muted">Chưa có lịch hẹn sắp tới.</td></tr>@endforelse
  </tbody></table></div></div>
  <div class="card"><div class="card-header bg-white"><strong>Luật sư gần đây</strong></div><div class="card-body"><div class="row g-3">@forelse($recentLawyers as $lawyer)<div class="col-md-6 col-lg-3"><div class="lawyer-card h-100 p-3"><div class="d-flex gap-2 align-items-center"><div class="lawyer-avatar">{{ strtoupper(mb_substr($lawyer->user->name,0,1)) }}</div><div><strong>{{ $lawyer->user->name }}</strong><div class="small text-muted">{{ $lawyer->title ?: 'Luật sư' }}</div></div></div><div class="small mt-3">{{ $lawyer->location->name ?? $lawyer->location->province ?? 'Chưa cập nhật địa điểm' }}</div><a href="{{ route('lawyers.show',$lawyer->id) }}" class="btn btn-sm btn-outline-primary mt-3">Xem hồ sơ</a></div></div>@empty<div class="col-12 text-muted">Chưa có luật sư đã xác minh.</div>@endforelse</div></div></div>
  </div></main>
</div>
@endsection
