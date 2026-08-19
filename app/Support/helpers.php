<?php

use App\Models\Store;

if (! function_exists('current_store')) {
    /**
     * Resolves the store the current request is acting on: the session's
     * chosen store (set by the store switcher) falling back to the
     * authenticated user's assigned home store. Returns null if neither is
     * set (e.g. an unassigned Admin/Manager viewing an "all stores" screen).
     */
    function current_store(): ?Store
    {
        $storeId = session('current_store_id');

        if (! $storeId && auth()->check()) {
            $storeId = auth()->user()->store_id;
        }

        return $storeId ? Store::find($storeId) : null;
    }
}
