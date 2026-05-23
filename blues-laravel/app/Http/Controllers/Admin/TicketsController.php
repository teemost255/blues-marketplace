<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class TicketsController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('user')->latest()->paginate(20);
        return view('admin.tickets', compact('tickets'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate(['admin_reply' => 'required|string', 'status' => 'required|in:open,in_progress,resolved,closed']);
        $ticket->update(['admin_reply' => $request->admin_reply, 'status' => $request->status]);
        return back()->with('success', 'Reply sent.');
    }
}
