<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->latest()->paginate(10);
        return view('dashboard.support', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'  => 'required|string|max:200',
            'message'  => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high',
        ]);

        SupportTicket::create([
            'user_id'  => Auth::id(),
            'subject'  => $request->subject,
            'message'  => $request->message,
            'priority' => $request->priority,
            'status'   => 'open',
        ]);

        return back()->with('success', 'Ticket submitted. We\'ll respond within 24 hours.');
    }
}
