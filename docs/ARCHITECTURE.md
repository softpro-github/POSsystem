# Architecture — Softpro POS

Laravel 12, PHP 8.2+, MySQL, server-rendered Blade views with Alpine.js for interactivity, Tailwind CSS v3, `spatie/laravel-permission` for roles, `spatie/laravel-activitylog` for audit trails. No SPA framework, no separate API — every page is a real HTTP response.

## Multi-store data model

Every store-scoped table carries a `store_id`. There is **one shared chart of accounts and one ledger** across all stores (no per-store accounting split) — only inventory, sales, shifts, and purchasing are store-scoped.

**`current_store()`** (`app/Support/helpers.php`, autoloaded via composer.json's `"files"` key) is the single resolution point used everywhere a controller needs "which store is this request acting on":

```php
function current_store(): ?Store
{
    $storeId = session('current_store_id');           // set by the store switcher
    if (! $storeId && auth()->check()) {
        $storeId = auth()->user()->store_id;           // fall back to the user's home store
    }
    return $storeId ? Store::find($storeId) : null;
}
```

Session wins over the user's assigned store so Admin/Manager can switch context without it sticking permanently to their account. Cashiers effectively always resolve to their assigned store since there's no switcher UI shown to them.

**Stock is per-store**, not a flat column. `products.quantity` doesn't exist — it was deliberately dropped. Instead, `product_stores` (`product_id`, `store_id`, `quantity`, `reorder_level`) holds one row per product-per-store. `Product::getQuantityAttribute()` transparently reads the *current* store's row via `current_store()`, so most existing view/report code that says `$product->quantity` keeps working unchanged — it's just no longer a real column, it's a computed accessor. Use `$product->stockAt($store)` when you need a *specific* store's figure regardless of request context (e.g. iterating all stores in a scheduled command), and `$product->totalQuantity()` for a cross-store sum.

## Stock movements — the single source of truth

**`app/Services/StockService.php`** is the only place stock quantities change. Every method (`receivePurchase`, `sell`, `returnStock`, `returnPurchase`, `adjust`, `transfer`) wraps its work in a DB transaction that (1) row-locks the relevant `product_stores` row via `lockForUpdate()` to prevent race conditions under concurrent sales, (2) updates the quantity, and (3) writes an immutable `stock_movements` row recording what happened, by how much, and why (`type` enum: `purchase`, `sale`, `return`, `purchase_return`, `adjustment`, `transfer_out`, `transfer_in`). The running `balance_after` on each movement row means stock history is always reconstructable and auditable — never take a shortcut and mutate `product_stores.quantity` directly anywhere else in the codebase.

`transfer()` is the one method that touches two stores atomically in a single transaction — decrementing the source, incrementing the destination, writing a linked `transfer_out`/`transfer_in` pair referencing the same `StockTransfer` record. Stock transfers themselves follow a `pending → received` flow mirroring purchase orders: creating a transfer does *not* move stock, only the destination store's explicit "receive" confirmation calls `StockService::transfer()`.

## Accounting — double-entry, one gateway

**`app/Services/JournalService.php`** is the only way anything posts to the ledger. `postEntry(array $lines, ...)` takes an array of `{account: 'code', debit: x, credit: y}` lines, and:

1. Refuses to post if debits ≠ credits (within a 1-cent rounding tolerance).
2. Refuses to post into a locked fiscal period (`FiscalPeriod.is_locked`).
3. Writes a `JournalEntry` + `JournalEntryLine` rows inside a transaction.

**History is never edited or deleted.** Corrections go through `reverseEntry()`, which posts a new entry with every line's debit/credit swapped and links it back via `reversed_entry_id` — the original stays on the books, fully traceable. Every automatic posting site (sale completion, expense recording, shift cash-variance) calls `postEntry()` rather than writing `JournalEntryLine` rows directly.

Account codes referenced throughout the codebase (grep for these if extending): `1000`/`1010` (Cash/Bank), `1200` (Inventory), `5000` (COGS), `5400` (Cash variance/shrinkage). See `database/seeders/ChartOfAccountsSeeder.php` for the full chart.

## Tax engine

Flat `categories.tax_rate` doesn't exist anymore either. Instead: `tax_components` (atomic named rates, e.g. "VAT" 7.5%) are bundled into `tax_groups` via a `tax_group_components` pivot, with exactly one group flagged `is_default`. Resolution order (`Product::resolveTaxGroup()`):

```
product.tax_group_id  →  product.category.tax_group_id  →  TaxGroup::defaultGroup()
```

Whichever group wins, `TaxGroup::totalRate()` sums its *active* components into a single flat percentage — this is the one number that actually reaches the POS screen. The POS cart's client-side Alpine math (`resources/views/pos/index.blade.php`) only ever sees this single resolved rate per line item; it has no knowledge of components or groups at all, which is why the tax engine could be rebuilt from a flat column to a full component/group system without touching any client-side cart logic.

## Roles & permissions

`spatie/laravel-permission`, seeded in `database/seeders/RoleSeeder.php`. Every protected route uses `Route::middleware('permission:xyz')` groups in `routes/web.php`; every conditional nav link/UI element uses Blade's `@can`/`@canany` directives against the same permission strings — there's no separate "UI visibility" concept from "route access," they're driven by the exact same permission checks, so there's no route that's reachable but hidden or vice versa. The **Roles** admin screen (`app/Http/Controllers/Users/RoleController.php`) lets an Admin create custom roles via the same `syncPermissions()` call the seeder uses, grouped into the same sections the sidebar itself uses (`private const GROUPS` in that controller) so the checkbox UI matches the nav structure.

A route occasionally needs to be reachable by *either* of two different permissions for different roles (e.g. Shift History: Cashiers reach it via `access pos`, Managers via `manage shifts` since Managers don't do POS work) — Spatie's middleware supports this via a pipe: `permission:access pos|manage shifts`.

## Offline-capable POS (service worker)

`resources/js/sw.js` (built via `vite-plugin-pwa`, `injectManifest` strategy, output to `public/build/sw.js`) is served from `/service-worker.js` — a real Laravel route (`routes/web.php`), not a static file — specifically so the response can carry a `Service-Worker-Allowed: /` header, letting a worker that physically lives under `/build/` control the whole site including `/pos`.

Two caching behaviors:
- **Precache**: every built JS/CSS asset plus `offline.html`, populated on install.
- **Runtime cache** (`NetworkFirst`, 4s timeout): the last successful `/pos` page load, so a connectivity drop mid-shift still serves a working (if slightly stale) POS screen instead of an error.

**Known gap, not yet fully resolved**: reliably serving `/pos` from cache after the app is fully closed and reopened while genuinely offline was tested and found inconsistent during development — the underlying SW registration and Cache Storage both persist correctly across a browser restart, but the very first offline navigation after reopening didn't reliably serve from cache in testing. Worth a manual recheck (DevTools → Application → Service Workers → Offline checkbox → reload) if offline resilience across full app restarts is load-bearing for how this gets used.

Offline **sale queuing** is separate from the SW cache: `resources/js/offline-queue.js` writes unsynced sales to IndexedDB when the `pos/sync` request fails, and retries them automatically once `navigator.onLine` flips back — this is why a sale made offline still shows a (locally-generated) confirmation immediately, then reconciles with the server once connectivity returns.

## Frontend conventions

- **No SPA framework, minimal JS dependencies by design.** Interactivity is Alpine.js directives inline in Blade views; new features favor vanilla JS modules (`resources/js/*.js`) over adding a library. Command palette, theme toggle, PWA install prompt, and the nav loading indicator are all hand-rolled for this reason.
- **Embedding server data into Alpine `x-data`**: use a `<script type="application/json">` JSON-island + `JSON.parse(el.textContent)`, *not* `@json()` inlined directly into an HTML attribute — Blade's `@json()` doesn't reliably escape quotes for that context. Where `@json()` *is* used inline (smaller payloads), the surrounding attribute is always single-quoted (`x-data='...'`) specifically to survive it.
- **Theming**: `tailwind.config.js`'s semantic color tokens (`surface`, `border`, `ink`, `accent`) resolve to CSS custom properties (`rgb(var(--color-x) / <alpha-value>)`), not static hex — this is what makes the app-wide light/dark/auto toggle possible without touching any of the ~140 view files that already consume those token classes. The `[data-theme]` variable block and zero-flash bootstrap script live in `resources/views/partials/theme-init.blade.php`, included in every layout's `<head>` before Vite assets load.
- **Full-page navigation loading feedback**: `resources/js/nav-loading.js` shows a top progress bar synchronously (not after a delay) on any qualifying link click or form submit. This is deliberate — Chromium can tear down a page's JS execution context within milliseconds of a navigation starting, sometimes before a delayed `setTimeout` callback would even fire, so showing the bar immediately (accepting a brief flash on fast navigations) is the only reliable way to guarantee it's visible for the slow-navigation case it exists for.
- **Icons**: hand-authored inline SVGs (`resources/views/components/nav-icon.blade.php` centralizes the sidebar set), not an icon font/library — kept consistent by reusing the same stroke-width (1.6–1.8) and viewBox (`0 0 24 24`) convention throughout.

## Where to look for X

| Looking for... | Start here |
|---|---|
| A specific permission's exact grants | `database/seeders/RoleSeeder.php` |
| How stock moves for any operation | `app/Services/StockService.php` |
| How anything posts to the ledger | `app/Services/JournalService.php` |
| Store-scoping for a new feature | `current_store()` in `app/Support/helpers.php` |
| Sidebar nav structure / permission gates | `resources/views/layouts/navigation.blade.php` |
| Route groups & their permission middleware | `routes/web.php` |
| The POS cart's client-side logic | the `posCart()` Alpine component in `resources/views/pos/index.blade.php` |
| Service worker caching rules | `resources/js/sw.js` |
| Scheduled/background jobs | `routes/console.php` + `app/Console/Commands/` |
