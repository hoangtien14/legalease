<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['customer.user', 'lawyer.user', 'appointment'])
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->integer('rating')))
            ->when($request->filled('visibility'), fn ($q) => $q->where('is_visible', $request->visibility === '1'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggle(Review $review)
    {
        $review->update(['is_visible' => !$review->is_visible]);

        return back()->with('success', $review->is_visible
            ? 'Đã hiển thị đánh giá.'
            : 'Đã ẩn đánh giá khỏi trang công khai.');
    }
}
