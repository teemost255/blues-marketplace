<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;

class TransactionsController extends Controller
{
    public function index()
    {
        $transactions = WalletTransaction::with('user')->latest()->paginate(30);
        return view('admin.transactions', compact('transactions'));
    }
}
