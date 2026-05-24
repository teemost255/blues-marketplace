<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('admin_audit_log')
            ->leftJoin('admins_users', 'admin_audit_log.admin_id', '=', 'admins_users.id')
            ->select(
                'admin_audit_log.*',
                'admins_users.display_name as admin_display',
                'admins_users.role as admin_role'
            );

        if ($request->search) {
            $query->where('admin_audit_log.action', 'ilike', '%' . $request->search . '%');
        }
        if ($request->target_type) {
            $query->where('admin_audit_log.target_type', $request->target_type);
        }
        if ($request->admin_id) {
            $query->where('admin_audit_log.admin_id', $request->admin_id);
        }

        $logs       = $query->latest('admin_audit_log.created_at')->paginate(30)->withQueryString();
        $adminUsers = AdminUser::orderBy('display_name')->get();

        return view('admin.audit', compact('logs', 'adminUsers'));
    }
}
