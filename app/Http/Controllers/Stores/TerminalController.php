<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Terminal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TerminalController extends Controller
{
    public function index(): View
    {
        $terminals = Terminal::with('store')->withCount('shifts')->orderBy('store_id')->orderBy('name')->paginate(20);

        return view('terminals.index', compact('terminals'));
    }

    public function create(): View
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('terminals.create', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        Terminal::create($this->validated($request));

        return redirect()->route('terminals.index')->with('success', 'Terminal created.');
    }

    public function edit(Terminal $terminal): View
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();

        return view('terminals.edit', compact('terminal', 'stores'));
    }

    public function update(Request $request, Terminal $terminal): RedirectResponse
    {
        $terminal->update($this->validated($request));

        return redirect()->route('terminals.index')->with('success', 'Terminal updated.');
    }

    public function destroy(Terminal $terminal): RedirectResponse
    {
        if ($terminal->shifts()->exists() || $terminal->sales()->exists()) {
            return back()->with('error', 'Cannot delete a terminal that has shifts or sales recorded against it.');
        }

        $terminal->delete();

        return redirect()->route('terminals.index')->with('success', 'Terminal deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
