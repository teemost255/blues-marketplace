<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Announcement, Notification, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(20);
        $userCount     = User::count();
        return view('admin.announcements', compact('announcements', 'userCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type'    => 'required|in:info,success,warning,error',
        ]);

        $users     = User::all();
        $emailSent = false;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => $request->title,
                'message' => $request->message,
                'type'    => $request->type,
            ]);
        }

        if ($request->boolean('send_email')) {
            $subject = '[Blues Marketplace] ' . $request->title;
            $body    = $request->message;
            foreach ($users as $user) {
                try {
                    Mail::raw($body, function ($msg) use ($user, $subject) {
                        $msg->to($user->email, $user->name)->subject($subject);
                    });
                } catch (\Throwable $e) {
                }
            }
            $emailSent = true;
        }

        Announcement::create([
            'title'            => $request->title,
            'message'          => $request->message,
            'type'             => $request->type,
            'sent_by'          => session('admin_id'),
            'email_sent'       => $emailSent,
            'recipients_count' => $users->count(),
        ]);

        return back()->with('success', 'Announcement sent to ' . $users->count() . ' user(s)' . ($emailSent ? ' with email.' : '.'));
    }
}
