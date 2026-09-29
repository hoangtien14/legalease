<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $items = News::with('author')
            ->when($request->filled('keyword'), fn ($q) => $q->where(function ($x) use ($request) {
                $keyword = trim($request->string('keyword')->toString());
                $x->where('title', 'like', "%{$keyword}%")->orWhere('slug', 'like', "%{$keyword}%");
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.news.index', compact('items'));
    }

    public function create() { return view('admin.news.form', ['item' => new News(['status' => 'draft'])]); }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['author_id'] = auth()->id();
        $this->normalizePublication($data);
        News::create($data);
        return redirect()->route('admin.news.index')->with('success', 'Đã thêm bài viết.');
    }

    public function edit(News $news) { return view('admin.news.form', ['item' => $news]); }

    public function update(Request $request, News $news)
    {
        $data = $this->validateData($request, $news);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $this->normalizePublication($data);
        $news->update($data);
        return redirect()->route('admin.news.index')->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return back()->with('success', 'Đã xóa bài viết.');
    }

    private function validateData(Request $request, ?News $news = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:280', Rule::unique('news', 'slug')->ignore($news?->id)],
            'excerpt' => ['nullable', 'string', 'max:5000'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function normalizePublication(array &$data): void
    {
        if ($data['status'] === 'published') {
            $data['published_at'] = $data['published_at'] ?? now();
        } else {
            $data['published_at'] = null;
        }
    }
}
