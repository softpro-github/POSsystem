<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(): View
    {
        $health = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'database' => $this->checkDatabase(),
            'queue_connection' => config('queue.default'),
            'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
            'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null,
            'cache_driver' => config('cache.default'),
            'disk_free_bytes' => @disk_free_space(storage_path()),
            'storage_writable' => is_writable(storage_path()),
            'log' => $this->checkLog(),
        ];

        return view('system-health.index', compact('health'));
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');

            return ['connected' => true, 'driver' => config('database.default')];
        } catch (\Throwable $e) {
            return ['connected' => false, 'driver' => config('database.default'), 'error' => $e->getMessage()];
        }
    }

    private function checkLog(): array
    {
        $path = storage_path('logs/laravel.log');

        if (! file_exists($path)) {
            return ['exists' => false];
        }

        return [
            'exists' => true,
            'size_bytes' => filesize($path),
            'modified_at' => date('Y-m-d H:i:s', filemtime($path)),
        ];
    }
}
