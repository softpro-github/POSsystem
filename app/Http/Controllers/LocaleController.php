<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|in:'.implode(',', array_keys(config('locales', []))),
        ]);

        return redirect()->back()->withCookie(
            cookie('gadgetstore_locale', $request->string('locale'), 60 * 24 * 365)
        );
    }
}
