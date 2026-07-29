<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SurePlusLogsService;
use Illuminate\Http\Request;

class ApiOrdersController extends Controller
{
    public function index(Request $request)
    {
        $svc = app(SurePlusLogsService::class);

        $page    = (int) $request->get('page', 1);
        $perPage = 20;

        $stats    = $svc->getStats();
        $orders   = $svc->getOrders($page, $perPage);
        $accounts = $svc->getAccounts($page, $perPage);

        $configured = $svc->isConfigured();

        return view('admin.api-orders', compact('stats', 'orders', 'accounts', 'configured', 'page', 'perPage'));
    }

    public function accountDetails(int $id)
    {
        $svc     = app(SurePlusLogsService::class);
        $account = $svc->getAccountDetails($id);

        if (!$account) {
            return response()->json(['error' => 'Account not found.'], 404);
        }

        return response()->json($account);
    }

    public function orderAccounts(int $orderId)
    {
        $svc      = app(SurePlusLogsService::class);
        $accounts = $svc->getAccountsByOrder($orderId);

        return response()->json($accounts);
    }
}
