<?php
namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\LawyerProfile;
use App\Models\LawyerSpecialization;
use App\Models\Specialization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không chính xác, hoặc tài khoản đã bị khóa.',
            ]);
        }

        $user = Auth::user();
        if (!$user->role || !in_array($user->role->name, ['customer', 'lawyer', 'super_admin'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages(['email' => 'Tài khoản chưa được cấu hình quyền truy cập hợp lệ.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'))
            ->with('success','Đăng nhập thành công!');
    }

    public function showRegister() { return view('auth.register'); }

    public function showLawyerRegister()
    {
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        $locations = \App\Models\Location::where('is_active', true)->orderBy('province')->orderBy('district')->get();
        return view('auth.lawyer-register', compact('specializations', 'locations'));
    }

    public function registerLawyer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'phone' => ['required','string','max:30'],
            'password' => ['required','string','min:8','confirmed'],
            'location_id' => ['required','integer','exists:locations,id'],
            'license_number' => ['required','string','max:100','unique:lawyer_profiles,license_number'],
            'title' => ['required','string','max:150'],
            'bio' => ['required','string','min:20','max:5000'],
            'education' => ['required','string','max:3000'],
            'experience' => ['required','string','max:3000'],
            'experience_years' => ['required','integer','min:0','max:80'],
            'consultation_fee' => ['required','numeric','min:0'],
            'specializations' => ['required','array','min:1'],
            'specializations.*' => ['integer','exists:specializations,id'],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if (!\App\Models\Location::whereKey($data['location_id'])->where('is_active', true)->exists()) {
            throw ValidationException::withMessages(['location_id' => 'Địa điểm hiện không khả dụng.']);
        }
        $activeSpecs = Specialization::whereIn('id', array_unique($data['specializations']))
            ->where('is_active', true)->count();
        if ($activeSpecs !== count(array_unique($data['specializations']))) {
            throw ValidationException::withMessages(['specializations' => 'Có chuyên ngành không còn hoạt động.']);
        }

        DB::transaction(function () use ($request, $data) {
            $role = Role::firstOrCreate(['name' => 'lawyer'], ['description' => 'Luật sư']);
            $user = User::create([
                'role_id' => $role->id, 'name' => $data['name'], 'email' => $data['email'],
                'phone' => $data['phone'], 'password' => Hash::make($data['password']), 'is_active' => true,
            ]);

            $avatar = $request->hasFile('avatar') ? $request->file('avatar')->store('profiles', 'public') : null;
            $profile = LawyerProfile::create([
                'user_id' => $user->id, 'location_id' => $data['location_id'], 'license_number' => $data['license_number'],
                'avatar' => $avatar, 'title' => $data['title'], 'bio' => $data['bio'], 'education' => $data['education'],
                'experience' => $data['experience'] ?? null, 'experience_years' => $data['experience_years'], 'consultation_fee' => $data['consultation_fee'], 'verification_status' => 'pending',
            ]);
            $profile->specializations()->sync(array_values(array_unique($data['specializations'])));
        });

        return redirect()->route('login')->with('success', 'Đăng ký luật sư thành công. Hồ sơ đang chờ Super Admin duyệt.');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'phone' => ['nullable','string','max:30'],
            'password' => ['required','string','min:8','confirmed'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $role = Role::firstOrCreate(
            ['name' => 'customer'],
            ['description' => 'Khách hàng sử dụng dịch vụ LegalEase']
        );

        $user = User::create([
            'role_id' => $role->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        CustomerProfile::create(['user_id' => $user->id]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home.index')
            ->with('success','Đăng ký tài khoản thành công!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home.index')
            ->with('success','Bạn đã đăng xuất.');
    }
}
