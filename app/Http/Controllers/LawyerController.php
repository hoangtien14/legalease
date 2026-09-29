<?php

namespace App\Http\Controllers;

use App\Models\LawyerProfile;
use App\Models\Location;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LawyerController extends Controller
{
    public function index(Request $request): View
    {
        $query = LawyerProfile::with(['user', 'location', 'specializations'])
            ->where('verification_status', 'verified')
            ->whereHas('location', fn($q) => $q->where('is_active', true))
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->withAvg(['reviews' => fn($q) => $q->where('is_visible', true)], 'rating')
            ->withCount(['reviews' => fn($q) => $q->where('is_visible', true)]);

        if ($request->filled('specialization_id')) {
            $query->whereHas('specializations', function ($q) use ($request) {
                $q->where('specializations.id', $request->integer('specialization_id'));
            });
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('province')) {
            $query->whereHas('location', fn($q) => $q->where('province', 'like', '%' . trim($request->province) . '%'));
        }

        if ($request->filled('min_experience')) {
            $query->where('experience_years', '>=', $request->integer('min_experience'));
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->string('keyword')->toString());
            $query->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'name' => $query->orderBy(User::select('name')->whereColumn('users.id', 'lawyer_profiles.user_id')),
            'experience' => $query->orderByDesc('experience_years')->orderBy('lawyer_profiles.id'),
            'fee_asc' => $query->orderBy('consultation_fee'),
            'fee_desc' => $query->orderByDesc('consultation_fee'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            default => $query->latest('lawyer_profiles.created_at'),
        };

        $lawyers = $query->paginate(12)->withQueryString();
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('province')->orderBy('district')->get();

        return view('lawyers.index', compact('lawyers', 'specializations', 'locations'));
    }

    public function show(int $id): View
    {
        $lawyer = LawyerProfile::with([
            'user', 'location', 'specializations', 'availabilitySlots', 'reviews.customer.user',
        ])->withAvg(['reviews' => fn($q) => $q->where('is_visible', true)], 'rating')->where('verification_status', 'verified')->whereHas('location', fn($q) => $q->where('is_active', true))->whereHas('user', fn($q) => $q->where('is_active', true))->findOrFail($id);

        return view('lawyers.show', compact('lawyer'));
    }
}
