<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const KEYS = [
        'store_name',
        'store_address',
        'store_phone',
        'currency_symbol',
        'receipt_footer',
        'low_stock_threshold_default',
        'bank_account_name',
        'bank_name',
        'bank_account_number',
    ];

    public function edit(): View
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Setting::get($key)]);
        $logoPath = Setting::get('store_logo');

        return view('settings.edit', compact('settings', 'logoPath'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['nullable', 'string', 'max:255'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:30'],
            'currency_symbol' => ['nullable', 'string', 'max:5'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'low_stock_threshold_default' => ['nullable', 'integer', 'min:0'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['boolean'],
        ]);

        foreach (self::KEYS as $key) {
            Setting::set($key, $validated[$key] ?? null);
        }

        if ($request->hasFile('logo')) {
            $oldPath = Setting::get('store_logo');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('branding', 'public');
            Setting::set('store_logo', $path);
        } elseif ($request->boolean('remove_logo')) {
            $oldPath = Setting::get('store_logo');
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            Setting::set('store_logo', null);
        }

        return redirect()->route('settings.edit')->with('success', 'Settings updated.');
    }
}
