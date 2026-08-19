<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreSwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
        ]);

        $request->session()->put('current_store_id', (int) $validated['store_id']);

        return back()->with('success', 'Switched store to '.Store::find($validated['store_id'])->name.'.');
    }
}
