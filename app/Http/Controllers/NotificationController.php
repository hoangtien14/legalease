<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);
        return view('notifications.index', compact('notifications'));
    }

    public function readAll(): RedirectResponse
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    public function read(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->update(['read_at' => now()]);
        return back();
    }
}
