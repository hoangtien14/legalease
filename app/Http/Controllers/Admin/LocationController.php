<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $items = Location::when($request->filled('keyword'), fn ($q) => $q->where(function ($x) use ($request) {
                $keyword = trim($request->string('keyword')->toString());
                $x->where('province', 'like', "%{$keyword}%")
                  ->orWhere('district', 'like', "%{$keyword}%");
            }))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active === '1'))
            ->orderBy('province')->orderBy('district')->paginate(15)->withQueryString();
        return view('admin.locations.index', compact('items'));
    }

    public function create() { return view('admin.locations.form', ['item' => new Location(['is_active' => true])]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        Location::create($data);
        return redirect()->route('admin.locations.index')->with('success', 'Đã thêm địa điểm.');
    }

    public function edit(Location $location) { return view('admin.locations.form', ['item' => $location]); }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'province' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $location->update($data);
        return redirect()->route('admin.locations.index')->with('success', 'Đã cập nhật địa điểm.');
    }

    public function destroy(Location $location)
    {
        if ($location->lawyerProfiles()->exists() || $location->customerProfiles()->exists()) {
            return back()->withErrors(['location' => 'Không thể xóa địa điểm đang được sử dụng. Hãy chuyển sang trạng thái không hoạt động.']);
        }
        $location->delete();
        return back()->with('success', 'Đã xóa địa điểm.');
    }
}
