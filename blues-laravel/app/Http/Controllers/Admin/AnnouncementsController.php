<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Announcement, Notification, Setting, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements   = Announcement::latest()->paginate(20);
        $userCount       = User::count();
        $smtpConfigured  = Setting::get('mail_host', '') !== ''
                        && Setting::get('mail_mailer', 'log') !== 'log';
        $fromAddress     = Setting::get('mail_from_address', '');

        return view('admin.announcements', compact('announcements', 'userCount', 'smtpConfigured', 'fromAddress'));
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
        $emailFail = 0;

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => $request->title,
                'message' => $request->message,
                'type'    => $request->type,
            ]);
        }

        if ($request->boolean('send_email')) {
            $siteName    = Setting::get('site_name', 'Blues Marketplace');
            $fromAddress = Setting::get('mail_from_address', config('mail.from.address'));
            $fromName    = Setting::get('mail_from_name', $siteName);
            $subject     = "[{$siteName}] " . $request->title;

            foreach ($users as $user) {
                try {
                    $html = view('emails.announcement', [
                        'user'     => $user,
                        'title'    => $request->title,
                        'message'  => $request->message,
                        'type'     => $request->type,
                        'siteName' => $siteName,
                    ])->render();

                    Mail::html($html, function ($msg) use ($user, $subject, $fromAddress, $fromName) {
                        $msg->to($user->email, $user->name)
                            ->from($fromAddress, $fromName)
                            ->subject($subject);
                    });
                } catch (\Throwable $e) {
                    $emailFail++;
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

        $msg = 'Announcement sent to ' . $users->count() . ' user(s)';
        if ($emailSent) {
            $sent = $users->count() - $emailFail;
            $msg .= ". Email delivered to {$sent}/" . $users->count();
            if ($emailFail > 0) $msg .= " ({$emailFail} failed)";
            $msg .= '.';
        } else {
            $msg .= '.';
        }

        return back()->with('success', $msg);
    }
}
