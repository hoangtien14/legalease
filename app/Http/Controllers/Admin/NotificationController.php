<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('user')->latest()->paginate(20);
        $roles = Role::orderBy('name')->get();
        return view('admin.notifications.index', compact('notifications','roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:200'],
            'message' => ['required','string','max:5000'],
            'type' => ['required','string','max:50'],
            'role_id' => ['nullable','integer','exists:roles,id'],
        ]);

        $query = User::where('is_active', true);
        if (!empty($data['role_id'])) $query->where('role_id', $data['role_id']);

        $count = 0;
        $query->select('id')->chunkById(500, function ($users) use ($data, &$count) {
            $rows = $users->map(fn($user) => [
                'user_id' => $user->id,
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();
            Notification::insert($rows);
            $count += count($rows);
        });

        return back()->with('success', "Đã gửi thông báo cho {$count} tài khoản đang hoạt động.");
    }
}
