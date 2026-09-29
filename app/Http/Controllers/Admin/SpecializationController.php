<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SpecializationController extends Controller
{
    public function index(Request $request)
    {
        $items = Specialization::when($request->filled('keyword'), fn ($q) => $q->where('name', 'like', '%' . trim($request->string('keyword')->toString()) . '%'))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active === '1'))
            ->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.specializations.index', compact('items'));
    }

    public function create() { return view('admin.specializations.form', ['item' => new Specialization(['is_active' => true])]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:specializations,name'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:specializations,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        Specialization::create($data);
        return redirect()->route('admin.specializations.index')->with('success', 'Đã thêm chuyên môn.');
    }

    public function edit(Specialization $specialization) { return view('admin.specializations.form', ['item' => $specialization]); }

    public function update(Request $request, Specialization $specialization)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('specializations', 'name')->ignore($specialization->id)],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('specializations', 'slug')->ignore($specialization->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $specialization->update($data);
        return redirect()->route('admin.specializations.index')->with('success', 'Đã cập nhật chuyên môn.');
    }

    public function destroy(Specialization $specialization)
    {
        if ($specialization->lawyers()->exists() || $specialization->appointments()->exists()) {
            return back()->withErrors(['specialization' => 'Không thể xóa chuyên môn đang được sử dụng. Hãy chuyển sang trạng thái không hoạt động.']);
        }
        $specialization->delete();
        return back()->with('success', 'Đã xóa chuyên môn.');
    }
}
