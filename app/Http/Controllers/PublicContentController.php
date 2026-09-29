<?php
namespace App\Http\Controllers;

use App\Models\{Faq, News};
use Illuminate\View\View;

class PublicContentController extends Controller
{
    public function announcements(): View
    {
        return view('public.announcements', [
            'news' => News::where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->orderByDesc('published_at')->paginate(10),
        ]);
    }

    public function faq(): View
    {
        return view('public.faq', ['faqs' => Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()]);
    }
}
