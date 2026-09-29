<?php

namespace App\Http\Controllers;

use App\Models\LawyerProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        return match ($user->role?->name) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'lawyer' => redirect()->route('lawyer.dashboard'),
            'customer' => $this->customerDashboard(),
            default => redirect()->route('home.index'),
        };
    }

    private function customerDashboard(): View
    {
        $customer = auth()->user()->customerProfile;
        abort_unless($customer, 403);

        $query = $customer->appointments();
        $stats = [
            'upcoming' => (clone $query)->whereIn('status', ['pending','confirmed','rescheduled'])
                ->where('appointment_at', '>=', now())->count(),
            'total' => (clone $query)->count(),
            'confirmed' => (clone $query)->whereIn('status', ['confirmed','rescheduled'])->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $appointments = $customer->appointments()
            ->with(['lawyer.user', 'specialization'])
            ->whereIn('status', ['pending','confirmed','rescheduled'])
            ->where('appointment_at', '>=', now())
            ->orderBy('appointment_at')
            ->take(5)->get();

        $recentLawyers = LawyerProfile::with(['user','location','specializations'])
            ->where('verification_status', 'verified')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->latest()->take(4)->get();

        return view('customer.dashboard', compact('customer','stats','appointments','recentLawyers'));
    }
}
