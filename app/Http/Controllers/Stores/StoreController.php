<?php

namespace App\Http\Controllers\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = Store::withCount('users')->orderBy('name')->paginate(20);

        return view('stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Store::create($this->validated($request));

        return redirect()->route('stores.index')->with('success', 'Store created.');
    }

    public function edit(Store $store): View
    {
        return view('stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $store->update($this->validated($request));

        return redirect()->route('stores.index')->with('success', 'Store updated.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        if ($store->users()->exists()) {
            return back()->with('error', 'Cannot delete a store that has users assigned to it.');
        }

        $store->delete();

        return redirect()->route('stores.index')->with('success', 'Store deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
