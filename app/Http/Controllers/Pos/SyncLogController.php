<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\View\View;

class SyncLogController extends Controller
{
    public function index(): View
    {
        $syncedSales = Sale::with(['customer', 'user'])
            ->whereNotNull('offline_queued_at')
            ->latest('offline_queued_at')
            ->paginate(30);

        return view('pos.sync-log', compact('syncedSales'));
    }
}
