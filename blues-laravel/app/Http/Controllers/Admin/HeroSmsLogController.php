<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSmsLogController extends Controller
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    public function index()
    {
        $entries = $this->parseLog(300);
        return view('admin.herosms-log', compact('entries'));
    }

    public function stream(Request $request)
    {
        $since   = (int) $request->query('since', 0);
        $entries = $this->parseLog(200);

        if ($since > 0) {
            $entries = array_filter($entries, fn($e) => $e['ts'] > $since);
        }

        return response()->json([
            'entries' => array_values($entries),
            'now'     => time(),
        ]);
    }

    private function parseLog(int $limit): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }

        $lines   = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            // Start of a new log entry: [YYYY-MM-DD HH:MM:SS] env.LEVEL: ...
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }
                $current = [
                    'time'    => $m[1],
                    'ts'      => strtotime($m[1]),
                    'level'   => strtolower($m[2]),
                    'message' => $m[3],
                    'context' => '',
                ];
            } elseif ($current !== null) {
                $current['context'] .= $line . "\n";
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        // Keep only HeroSMS-related entries
        $herosms = array_filter($entries, fn($e) =>
            stripos($e['message'], 'HeroSMS') !== false ||
            stripos($e['message'], 'VirtualNumber') !== false ||
            stripos($e['message'], 'herosms') !== false
        );

        // Return newest first, capped at $limit
        $herosms = array_reverse(array_values($herosms));
        return array_slice($herosms, 0, $limit);
    }
}
