<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'LegalEase')</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
@stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-legalease sticky-top">
<div class="container">
<a class="navbar-brand" href="{{ route('home.index') }}"><i class="fa-solid fa-scale-balanced me-2"></i>LegalEase</a>
<button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="navMain">
<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
<li class="nav-item"><a class="nav-link {{ request()->routeIs('home.index','home') ? 'active' : '' }}" href="{{ route('home.index') }}">Trang chủ</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('lawyers.*') ? 'active' : '' }}" href="{{ route('lawyers.index') }}">Tìm luật sư</a></li>
<li class="nav-item"><a class="nav-link {{ request()->is('announcements') ? 'active' : '' }}" href="{{ url('/announcements') }}">Tin tức pháp lý</a></li>
<li class="nav-item"><a class="nav-link {{ request()->is('faq') ? 'active' : '' }}" href="{{ url('/faq') }}">Câu hỏi thường gặp</a></li>
</ul>
<ul class="navbar-nav align-items-lg-center">
@guest
<li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Đăng nhập</a></li>
<li class="nav-item ms-lg-2"><a class="btn btn-warning" href="{{ route('register') }}">Đăng ký</a></li>
@endguest
@auth
<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fa-solid fa-user me-1"></i>{{ auth()->user()->name }}</a><ul class="dropdown-menu dropdown-menu-end">
@if(auth()->user()->isSuperAdmin())<li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Bảng quản trị</a></li>@elseif(auth()->user()->isLawyer())<li><a class="dropdown-item" href="{{ route('lawyer.dashboard') }}">Bảng luật sư</a></li>@else<li><a class="dropdown-item" href="{{ route('customer.dashboard') }}">Bảng khách hàng</a></li>@endif
<li><a class="dropdown-item" href="{{ route('profile.edit') }}">Hồ sơ</a></li><li><hr class="dropdown-divider"></li><li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item">Đăng xuất</button></form></li></ul></li>
@endauth
</ul></div></div></nav>
@if(session('success'))<div class="container mt-3"><div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>@endif
@if(session('error'))<div class="container mt-3"><div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>@endif
@yield('content')
<footer><div class="container"><div class="row g-4"><div class="col-md-4"><h5><i class="fa-solid fa-scale-balanced me-2"></i>LegalEase</h5><p class="small">Tìm luật sư đã xác minh cho nhu cầu pháp lý của bạn, nhanh chóng và dễ dàng.</p></div><div class="col-md-4"><h5>Liên kết nhanh</h5><ul class="list-unstyled small"><li><a href="{{ route('home.index') }}">Trang chủ</a></li><li><a href="{{ route('lawyers.index') }}">Tìm luật sư</a></li><li><a href="{{ url('/faq') }}">Câu hỏi thường gặp</a></li><li><a href="{{ url('/announcements') }}">Tin tức pháp lý</a></li><li><a href="{{ route('sitemap') }}">Sitemap</a></li></ul></div><div class="col-md-4"><h5>Liên hệ</h5><p class="small mb-1"><i class="fa-solid fa-envelope me-2"></i>support@legalease.com</p><p class="small"><i class="fa-solid fa-phone me-2"></i>+84 900 000 000</p></div></div><hr class="border-secondary"><p class="text-center small mb-0">&copy; 2026 LegalEase. Đã đăng ký bản quyền. (Dự án demo sinh viên)</p></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="{{ asset('js/legalease.js') }}"></script>@stack('scripts')
</body></html>