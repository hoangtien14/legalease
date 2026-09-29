<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\Faq; use Illuminate\Http\Request;
class FaqController extends Controller {
 public function index(){ $items=Faq::orderBy('sort_order')->paginate(15); return view('admin.faqs.index',compact('items')); }
 public function create(){return view('admin.faqs.form',['item'=>new Faq]);}
 public function store(Request $r){$d=$r->validate(['question'=>'required|max:255','answer'=>'required','sort_order'=>'nullable|integer','is_active'=>'boolean']);$d['is_active']=$r->boolean('is_active');Faq::create($d);return redirect()->route('admin.faqs.index')->with('success','Đã thêm FAQ.');}
 public function edit(Faq $faq){return view('admin.faqs.form',['item'=>$faq]);}
 public function update(Request $r,Faq $faq){$d=$r->validate(['question'=>'required|max:255','answer'=>'required','sort_order'=>'nullable|integer','is_active'=>'boolean']);$d['is_active']=$r->boolean('is_active');$faq->update($d);return redirect()->route('admin.faqs.index')->with('success','Đã cập nhật FAQ.');}
 public function destroy(Faq $faq){$faq->delete();return back()->with('success','Đã xóa FAQ.');}
}
