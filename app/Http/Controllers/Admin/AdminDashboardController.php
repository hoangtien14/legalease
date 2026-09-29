<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{User,LawyerProfile,CustomerProfile,Appointment,Review,Specialization,Location,News,Faq};
class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'users'=>User::count(), 'lawyers'=>LawyerProfile::count(),
            'customers'=>CustomerProfile::count(), 'appointments'=>Appointment::count(),
            'reviews'=>Review::count(), 'specializations'=>Specialization::count(),
            'locations'=>Location::count(), 'news'=>News::count(), 'faqs'=>Faq::count(),
            'pendingLawyers'=>LawyerProfile::where('verification_status','pending')->count(),
        ]);
    }
}
