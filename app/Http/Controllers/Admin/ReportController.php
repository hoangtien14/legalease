<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Appointment, CustomerProfile, LawyerProfile, Review, User};
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $base = Appointment::query()->when($from, fn($q)=>$q->where('appointment_at','>=',$from))->when($to, fn($q)=>$q->where('appointment_at','<=',$to));
        $stats = [
            'users'=>User::count(), 'lawyers'=>LawyerProfile::count(), 'customers'=>CustomerProfile::count(),
            'appointments'=>$base->count(), 'completed'=>(clone $base)->where('status','completed')->count(),
            'cancelled'=>(clone $base)->where('status','cancelled')->count(), 'pending'=>(clone $base)->where('status','pending')->count(),
            'reviews'=>Review::count(),
        ];
        $topLawyers = LawyerProfile::with('user')->withCount(['appointments as completed_appointments'=>fn($q)=>$q->where('status','completed')])->orderByDesc('completed_appointments')->limit(10)->get();
        return view('admin.reports.index', compact('stats','topLawyers','from','to'));
    }
}
