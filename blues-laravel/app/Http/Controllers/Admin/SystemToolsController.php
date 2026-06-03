<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemToolsController extends Controller
{
    public function index()
    {
        $tables   = $this->getTables();
        $logLines = $this->getLogLines(100);
        return view('admin.system-tools', compact('tables', 'logLines'));
    }

    public function runMigrations(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            return response()->json(['success' => true, 'output' => $output ?: 'Nothing to migrate.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }

    public function clearCaches(Request $request)
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return response()->json(['success' => true, 'output' => "Config, route, view, and application cache cleared successfully."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'output' => $e->getMessage()], 500);
        }
    }

    public function getLog(Request $request)
    {
        $lines = $this->getLogLines((int) $request->input('lines', 100));
        return response()->json(['lines' => $lines]);
    }

    private function getTables(): array
    {
        try {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                $rows = DB::select('SHOW TABLES');
                return array_map(fn($r) => array_values((array) $r)[0], $rows);
            } elseif ($driver === 'pgsql') {
                $rows = DB::select("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename");
                return array_map(fn($r) => $r->tablename, $rows);
            }
            return [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function getLogLines(int $count): array
    {
        $path = storage_path('logs/laravel.log');
        if (!file_exists($path)) {
            return ['No log file found at ' . $path];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return ['Log file is empty.'];
        }
        return array_slice($lines, -$count);
    }
}
