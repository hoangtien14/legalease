<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Role};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role')
            ->when($request->filled('keyword'), fn ($q) => $q->where(fn ($x) => $x->where('name', 'like', '%' . trim($request->string('keyword')->toString()) . '%')->orWhere('email', 'like', '%' . trim($request->string('keyword')->toString()) . '%')))
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->integer('role_id')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active === '1'))
            ->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create() { return view('admin.users.form', ['user' => new User(['is_active' => true]), 'roles' => Role::orderBy('name')->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active');
        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'Đã thêm người dùng.');
    }

    public function edit(User $user) { return view('admin.users.form', ['user' => $user, 'roles' => Role::orderBy('name')->get()]); }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $targetRole = Role::findOrFail($data['role_id']);
        $currentIsSuperAdmin = $user->isSuperAdmin();
        $targetIsSuperAdmin = $targetRole->name === 'super_admin';
        $superAdminCount = User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->count();

        if ($user->id === auth()->id() && !$targetIsSuperAdmin) {
            return back()->withErrors(['role_id' => 'Không thể tự hạ quyền tài khoản Super Admin đang đăng nhập.'])->withInput();
        }
        if ($currentIsSuperAdmin && !$targetIsSuperAdmin && $superAdminCount <= 1) {
            return back()->withErrors(['role_id' => 'Hệ thống phải luôn có ít nhất một Super Admin.'])->withInput();
        }

        if (array_key_exists('password', $data) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['is_active'] = $request->boolean('is_active');

        if ($user->id === auth()->id() && !$data['is_active']) {
            return back()->withErrors(['is_active' => 'Không thể tự vô hiệu hóa tài khoản đang đăng nhập.'])->withInput();
        }
        if ($currentIsSuperAdmin && !$data['is_active'] && $superAdminCount <= 1) {
            return back()->withErrors(['is_active' => 'Không thể vô hiệu hóa Super Admin cuối cùng.'])->withInput();
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật người dùng.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Không thể xóa tài khoản đang đăng nhập.']);
        }
        if ($user->isSuperAdmin() && User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->count() <= 1) {
            return back()->withErrors(['user' => 'Không thể xóa Super Admin cuối cùng.']);
        }
        if ($user->customerProfile?->appointments()->exists() || $user->lawyerProfile?->appointments()->exists()) {
            return back()->withErrors(['user' => 'Không thể xóa người dùng đã có lịch hẹn. Hãy vô hiệu hóa tài khoản để bảo toàn lịch sử.']);
        }
        $user->delete();
        return back()->with('success', 'Đã xóa người dùng.');
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['user' => 'Không thể tự khóa tài khoản đang đăng nhập.']);
        if ($user->isSuperAdmin() && $user->is_active && User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['user' => 'Không thể khóa Super Admin đang hoạt động cuối cùng.']);
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', $user->is_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.');
    }
}
