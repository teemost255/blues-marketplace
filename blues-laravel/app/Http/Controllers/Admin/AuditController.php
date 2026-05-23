<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        $logs = DB::table('admin_audit_log')->leftJoin('admins_users', 'admin_audit_log.admin_id', '=', 'admins_users.id')
            ->select('admin_audit_log.*', 'admins_users.email as admin_email')->latest('admin_audit_log.created_at')->paginate(30);
        return view('admin.audit', compact('logs'));
    }
}
