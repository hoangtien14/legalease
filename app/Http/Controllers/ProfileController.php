<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user()->load(['customerProfile.location', 'lawyerProfile.location', 'lawyerProfile.specializations']);
        $locations = Location::where('is_active', true)->orderBy('province')->orderBy('district')->get();
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        return view('profile.edit', compact('user', 'locations', 'specializations'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'license_number' => ['nullable', 'string', 'max:100', Rule::unique('lawyer_profiles', 'license_number')->ignore($user->lawyerProfile?->id)],
            'title' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'education' => ['nullable', 'string', 'max:3000'],
            'experience' => ['nullable', 'string', 'max:3000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'specialization_ids' => ['nullable', 'array'],
            'specialization_ids.*' => ['integer', 'exists:specializations,id'],
        ]);

        if (!empty($data['location_id']) && !Location::whereKey($data['location_id'])->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['location_id' => 'Địa điểm hiện không hoạt động.']);
        }

        DB::transaction(function () use ($request, $data, $user) {
            $user->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);

            if ($user->isCustomer()) {
                $profile = $user->customerProfile()->firstOrCreate(['user_id' => $user->id]);
                $profileData = [
                    'location_id' => $data['location_id'] ?? null,
                    'address' => $data['address'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                ];
                if ($request->hasFile('avatar')) $profileData['avatar'] = $request->file('avatar')->store('profiles', 'public');
                $profile->update($profileData);
            }

            if ($user->isLawyer()) {
                $profile = $user->lawyerProfile()->firstOrCreate(['user_id' => $user->id]);
                $specIds = array_values(array_unique($data['specialization_ids'] ?? $profile->specializations()->pluck('specializations.id')->all()));
                if (count($specIds) !== Specialization::whereIn('id', $specIds)->where('is_active', true)->count()) {
                    throw ValidationException::withMessages(['specialization_ids' => 'Có chuyên ngành không còn hoạt động.']);
                }
                if ($profile->verification_status === 'verified' && (empty($data['location_id']) || empty($specIds))) {
                    throw ValidationException::withMessages(['location_id' => 'Luật sư đã xác minh phải có địa điểm và ít nhất một chuyên ngành hoạt động.']);
                }
                $profileData = [
                    'location_id' => $data['location_id'] ?? $profile->location_id,
                    'license_number' => $data['license_number'] ?? null,
                    'title' => $data['title'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'education' => $data['education'] ?? null,
                    'experience' => $data['experience'] ?? null,
                    'experience_years' => $data['experience_years'] ?? 0,
                    'consultation_fee' => $data['consultation_fee'] ?? 0,
                ];
                if ($request->hasFile('avatar')) $profileData['avatar'] = $request->file('avatar')->store('profiles', 'public');
                $profile->update($profileData);
                if ($request->has('specialization_ids_present')) $profile->specializations()->sync($specIds);
            }
        });

        return back()->with('success', 'Cập nhật hồ sơ thành công.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);
        auth()->user()->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Đổi mật khẩu thành công.');
    }
}
