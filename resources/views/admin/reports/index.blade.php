@extends('admin.layout')
@section('content')
<div class="container-fluid py-3">
<h2>Reports & Analytics</h2>
<form class="row g-2 mb-4" method="GET">
 <div class="col-md-3"><label>Từ ngày</label><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
 <div class="col-md-3"><label>Đến ngày</label><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
 <div class="col-md-2 align-self-end"><button class="btn btn-primary">Lọc báo cáo</button></div>
</form>
<div class="row g-3">
 @foreach($stats as $key=>$value)<div class="col-md-3"><div class="card p-3"><small>{{ ucfirst(str_replace('_',' ',$key)) }}</small><h3>{{ $value }}</h3></div></div>@endforeach
</div>
<div class="card mt-4"><div class="card-body"><h5>Lawyer activity</h5><table class="table"><thead><tr><th>Lawyer</th><th>Completed appointments</th></tr></thead><tbody>@foreach($topLawyers as $lawyer)<tr><td>{{ $lawyer->user->name }}</td><td>{{ $lawyer->completed_appointments }}</td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection
