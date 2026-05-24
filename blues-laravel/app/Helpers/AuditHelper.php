<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AuditHelper
{
    public static function log(string $action, string $targetType = null, $targetId = null, string $details = null): void
    {
        try {
            DB::table('admin_audit_log')->insert([
                'admin_id'    => session('admin_id'),
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'details'     => $details,
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable) {}
    }
}
