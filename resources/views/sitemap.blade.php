@extends('layouts.app')
@section('title','Sitemap - LegalEase')
@section('content')
<section class="page-header"><div class="container"><span class="hero-badge">⚖ LegalEase</span><h1>Sitemap</h1><p>Sơ đồ các khu vực chính của hệ thống LegalEase.</p></div></section>
<section class="py-5"><div class="container"><div class="row g-4">
@php($groups=[
'Khách truy cập'=>[['Trang chủ','home.index'],['Tìm luật sư','lawyers.index'],['Đăng nhập','login'],['Đăng ký','register'],['Đăng ký luật sư','lawyer.register']],
'Customer'=>[['Dashboard','dashboard'],['Hồ sơ','profile.edit'],['Lịch của tôi','customer.appointments.index'],['Thông báo','notifications.index']],
'Lawyer'=>[['Dashboard','lawyer.dashboard'],['Thông báo','notifications.index'],['Hồ sơ','profile.edit']],
'Super Admin'=>[['Dashboard','admin.dashboard'],['Người dùng','admin.users.index'],['Luật sư','admin.lawyers.index'],['Chuyên môn','admin.specializations.index'],['Địa điểm','admin.locations.index'],['Lịch hẹn','admin.appointments.index'],['FAQ','admin.faqs.index'],['Tin tức','admin.news.index'],['Báo cáo','admin.reports.index']]])
@foreach($groups as $title=>$items)<div class="col-md-6 col-lg-3"><div class="card h-100 shadow-sm"><div class="card-body"><h3 class="h5">{{ $title }}</h3><ul class="list-group list-group-flush">@foreach($items as [$label,$route])<li class="list-group-item px-0">@guest @if(in_array($route,['dashboard','profile.edit','customer.appointments.index','lawyer.dashboard','notifications.index','admin.dashboard','admin.users.index','admin.lawyers.index','admin.specializations.index','admin.locations.index','admin.appointments.index','admin.faqs.index','admin.news.index','admin.reports.index']))<span class="text-muted">{{ $label }}</span>@else<a href="{{ route($route) }}">{{ $label }}</a>@endif @else @if(Route::has($route))<a href="{{ route($route) }}">{{ $label }}</a>@endif @endguest</li>@endforeach</ul></div></div></div>@endforeach
</div></div></section>
@endsection
