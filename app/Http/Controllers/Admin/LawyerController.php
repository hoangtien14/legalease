<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{LawyerProfile, Location, Specialization};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\NotificationService;

class LawyerController extends Controller
{
    public function index(Request $request)
    {
        $query = LawyerProfile::with(['user','location','specializations'])
            ->withAvg(['reviews' => fn($q) => $q->where('is_visible', true)], 'rating');

        if ($request->filled('keyword')) {
            $keyword = trim($request->string('keyword')->toString());
            $query->whereHas('user', fn($q) => $q->where(fn($x) => $x->where('name','like',"%{$keyword}%")->orWhere('email','like',"%{$keyword}%")));
        }
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }
        if ($request->filled('specialization_id')) {
            $query->whereHas('specializations', fn($q) => $q->where('specializations.id', $request->integer('specialization_id')));
        }
        if ($request->filled('is_active')) {
            $active = $request->is_active === '1';
            $query->whereHas('user', fn($q) => $q->where('is_active', $active));
        }

        $lawyers = $query->latest('lawyer_profiles.created_at')->paginate(15)->withQueryString();
        $locations = Location::where('is_active', true)->orderBy('province')->orderBy('district')->get();
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        return view('admin.lawyers.index', compact('lawyers','locations','specializations'));
    }

    public function show(LawyerProfile $lawyer)
    {
        $lawyer->load(['user','location','specializations','availabilitySlots','reviews']);
        return view('admin.lawyers.show', compact('lawyer'));
    }


    public function edit(LawyerProfile $lawyer)
    {
        $lawyer->load(['user','specializations']);
        $locations = Location::where('is_active', true)->orderBy('province')->orderBy('district')->get();
        $specializations = Specialization::where('is_active', true)->orderBy('name')->get();
        return view('admin.lawyers.form', compact('lawyer','locations','specializations'));
    }

    public function update(Request $request, LawyerProfile $lawyer)
    {
        $lawyer->load('user');
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$lawyer->user_id],
            'phone' => ['nullable','string','max:30'],
            'location_id' => ['nullable','integer','exists:locations,id'],
            'license_number' => ['nullable','string','max:100', Rule::unique('lawyer_profiles', 'license_number')->ignore($lawyer->id)],
            'title' => ['nullable','string','max:150'],
            'bio' => ['nullable','string','max:5000'],
            'education' => ['nullable','string','max:5000'],
            'experience' => ['nullable','string','max:3000'],
            'experience_years' => ['nullable','integer','min:0','max:80'],
            'consultation_fee' => ['required','numeric','min:0','max:999999999'],
            'verification_status' => ['required','in:pending,verified,rejected'],
            'specialization_ids' => ['nullable','array'],
            'specialization_ids.*' => ['integer','exists:specializations,id'],
            'is_active' => ['nullable','boolean'],
        ]);

        if (!empty($data['location_id']) && !Location::whereKey($data['location_id'])->where('is_active', true)->exists()) {
            return back()->withErrors(['location_id' => 'Địa điểm hiện không hoạt động.'])->withInput();
        }
        $specIds = array_values(array_unique($data['specialization_ids'] ?? []));
        if ($data['verification_status'] === 'verified') {
            if (empty($data['location_id']) || !Location::whereKey($data['location_id'])->where('is_active', true)->exists()) {
                return back()->withErrors(['location_id' => 'Luật sư đã xác minh phải có địa điểm hoạt động hợp lệ.'])->withInput();
            }
            if (empty($specIds) || count($specIds) !== Specialization::whereIn('id', $specIds)->where('is_active', true)->count()) {
                return back()->withErrors(['specialization_ids' => 'Luật sư đã xác minh phải có ít nhất một chuyên ngành hoạt động.'])->withInput();
            }
        }
        if (count($specIds) !== Specialization::whereIn('id', $specIds)->where('is_active', true)->count()) {
            return back()->withErrors(['specialization_ids' => 'Có chuyên ngành không còn hoạt động.'])->withInput();
        }

        DB::transaction(function () use ($data, $lawyer, $specIds) {
            $lawyer->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => (bool)($data['is_active'] ?? false),
            ]);
            $lawyer->update([
                'location_id' => $data['location_id'] ?? null,
                'license_number' => $data['license_number'] ?? null,
                'title' => $data['title'] ?? null,
                'bio' => $data['bio'] ?? null,
                'education' => $data['education'] ?? null,
                'experience' => $data['experience'] ?? null,
                'experience_years' => $data['experience_years'] ?? 0,
                'consultation_fee' => $data['consultation_fee'],
                'verification_status' => $data['verification_status'],
            ]);
            $lawyer->specializations()->sync($specIds);
        });

        return redirect()->route('admin.lawyers.show', $lawyer)->with('success','Đã cập nhật hồ sơ luật sư.');
    }

    public function verify(Request $request, LawyerProfile $lawyer)
    {
        $data = $request->validate(['status'=>'required|in:pending,verified,rejected']);
        if ($data['status'] === 'verified') {
            if (!$lawyer->location_id || !Location::whereKey($lawyer->location_id)->where('is_active', true)->exists()) {
                return back()->withErrors(['status' => 'Không thể duyệt luật sư khi chưa có địa điểm hoạt động hợp lệ.']);
            }
            if (!$lawyer->specializations()->where('is_active', true)->exists()) {
                return back()->withErrors(['status' => 'Không thể duyệt luật sư khi chưa có chuyên ngành hoạt động.']);
            }
            $lawyer->user?->update(['is_active'=>true]);
        }
        $lawyer->update(['verification_status'=>$data['status']]);
        if ($lawyer->user) {
            NotificationService::send($lawyer->user, 'Cập nhật trạng thái hồ sơ', 'Trạng thái hồ sơ luật sư của bạn đã được cập nhật thành: ' . $data['status'] . '.', 'admin', route('profile.edit'));

        }
        return back()->with('success','Đã cập nhật trạng thái duyệt luật sư.');
    }

    public function toggle(LawyerProfile $lawyer)
    {
        abort_if(!$lawyer->user, 404);
        $lawyer->user->update(['is_active' => !$lawyer->user->is_active]);
        return back()->with('success', $lawyer->user->is_active ? 'Đã kích hoạt luật sư.' : 'Đã vô hiệu hóa luật sư.');
    }

    public function destroy(LawyerProfile $lawyer)
    {
        if ($lawyer->appointments()->exists()) {
            return back()->withErrors(['lawyer' => 'Không thể xóa luật sư đã có lịch hẹn. Hãy vô hiệu hóa tài khoản để bảo toàn lịch sử.']);
        }
        $lawyer->user?->delete();
        return back()->with('success','Đã xóa luật sư.');
    }
}
