# User Guide — Softpro POS

## Roles

Three roles ship by default (`database/seeders/RoleSeeder.php`):

- **Admin** — full access to everything, including Users, Roles, Tax Settings, System Health.
- **Manager** — day-to-day store operations and reporting (products, stock, purchasing, customers, suppliers, discounts, shifts, accounting, expenses, store management) but not user/role management or system config.
- **Cashier** — Dashboard, POS, Sales History, Customers, Shift History. Enough to run the till, nothing that changes store configuration.

Roles and their exact permissions are managed at **Admin → Roles** (Admin only). Creating a custom role there uses the same permission set the app enforces everywhere else — the sidebar and every route automatically respect it.

## Setting up your store — first-time tasks

Do these roughly in order when setting up a new store; each one unblocks the next.

### 1. Create a store (only if you have more than one location)

**Admin → Stores → Add Store.** Fields: **Name**, **Address** (optional), **Phone** (optional). A single-location business can skip this entirely — one "Main Store" is created automatically when the app is first seeded, and everything works fine with just that one.

### 2. Create categories, brands, and units (product prerequisites)

Products need a **Unit** (required) and can optionally have a **Category** and **Brand** — set these up first so they're available in the product form's dropdowns.

- **Inventory → Units → Add Unit** — e.g. "Piece" (abbreviation "pc"), "Kilogram" ("kg"), "Box" ("bx"). Every product needs one.
- **Inventory → Categories → Add Category** — e.g. "Phones", "Accessories". Optional, but powers the category filter pills on the POS screen and can carry its own tax group.
- **Inventory → Brands → Add Brand** — optional, e.g. "Samsung", "Apple".

### 3. Create a product

**Inventory → Products → Add Product.** Two fields are easy to confuse: **SKU** (Stock Keeping Unit) is your own internal reference code for the product — short, human-chosen, used for searching and reports (e.g. `PHN-IP13`). **Barcode** is the number an actual barcode scanner reads off the item or a printed label — it's a different thing and can be left blank if the product doesn't have one yet.

Key fields:

| Field | Notes |
|---|---|
| Name | Required. |
| Category | Optional — powers POS category filters. |
| **SKU** | Required — see above. |
| Barcode | Optional — see above. |
| Brand / Unit | Unit is required, Brand optional. |
| Cost Price / Selling Price | Both required — cost price feeds COGS/margin reporting, selling price is what customers pay. |
| Reorder Level | Triggers the low-stock notification and dashboard count once on-hand quantity drops to or below this number. |
| Tax Group | Optional — leave blank to inherit the category's tax group, or the store-wide default if the category has none either. |
| Track IMEI/Serial numbers | Check this for serialized items (phones, laptops) — the POS will then require picking a specific serial at sale time instead of just a quantity. |
| Product Photo | Optional — shown on the POS product grid; falls back to a placeholder icon if left blank. |

Saving a product with **no stock yet** is normal — it just won't be sellable at any store until stock is added, via either a Purchase Order receipt or a manual Stock Reconciliation/Adjustment (see below).

### 4. Get stock into the system

Two ways to get a product's quantity above zero for a store:

- **Purchases → New Purchase Order** — pick a supplier, add line items (product + quantity + cost), save it, then open it and click **Receive Stock** once the goods physically arrive. This is the normal restocking flow and is what actually increments stock — creating the PO alone does not.
- **Inventory → Stock Reconciliation** (for correcting/setting an initial count without a supplier involved) — lists current book quantity per product, you enter the actual counted quantity, and it auto-adjusts the difference with a reason attached.

### 5. Create a terminal (optional — only for stores with more than one till)

**Admin → Terminals → Add Terminal.** Fields: **Store**, **Name** (e.g. "Register 1"), **Active**. If a store has zero terminals configured, the shift-open screen simply skips asking which one you're on — nothing else needs to be set up for a single-till store.

### 6. Create users and assign roles

**Admin → Users → Add User** — name, email, password, assigned **Store** (which store they belong to and default into), and **Role** (Admin/Manager/Cashier, or a custom role created at Admin → Roles).

## Signing in and shifts

Every cashier-facing session starts with opening a **shift**:

1. Log in — if no shift is currently open for you, you're redirected straight to **Start Shift** automatically (also reachable any time at Sidebar → My Shift).
2. Enter the **Opening Float** — the cash physically in the till before the first sale (enter `0` if starting empty).
3. If the store has active Terminals configured, also pick which till/register you're on.
4. Click **Start Shift** — you land on the POS screen, ready to sell.

A shift stays open until you explicitly close it (from Sidebar → My Shift, click **Close Shift**). Closing asks you to count and enter the actual cash in the drawer; the system compares that against the *expected* cash (opening float + cash sales − cash refunds − cash paid to suppliers) and flags a **variance** if they don't match beyond the configured tolerance (Settings → Cash Variance Tolerance). A variance over tolerance requires picking a reason (managed at Settings → Cash Mismatch Reasons) before the shift can close.

**Shift History** (visible to Cashiers and up) shows every shift, past and present, across the whole store — not just your own. Opening one shows either:
- **X-Report** (open shift, live) — a running total: sales, tax, discounts, refunds, payments by method, and the current expected cash.
- **Z-Report** (closed shift, frozen) — the same breakdown locked at close time, plus the counted cash, the variance, and the reason if one applied. Closed shifts have a "Print Z-Report" button.

## The POS screen

Three layout modes, switchable from the toolbar (your choice is remembered per-device):

- **Beam** — product grid on the left, cart on the right. Default.
- **Lane** — same product grid, cart mirrored to the left.
- **Counter** — a category sidebar on the left, a dense single-line product list in the middle, cart on the right. Better for a large catalog on a smaller screen.

**Finding products**: type in the search box, or scan a barcode directly into the barcode field (press Enter or let the scanner's own Enter-keystroke submit it — see the hardware notes below). Category pills filter the visible products; **Quick Picks** surfaces your best-sellers from the last 30 days automatically (falls back to alphabetical if there's no sales history yet).

**Keyboard shortcuts** (work anywhere on the POS screen):

| Key | Action |
|---|---|
| F2 | Focus the customer field |
| F3 | Focus the discount field |
| F4 | Hold the current order |
| F8 | Focus the barcode scan field |
| F9 | Complete the sale |

**Payment**: the amount field pre-fills with the total due automatically — you don't need to type it for a simple full-payment sale, only if you're taking a different amount (partial payment, or giving change). Editing it manually stops the auto-fill from overwriting your entry. Adding a second payment method (split payment) defaults its amount to whatever's still unpaid.

**After completing a sale**: a confirmation shows the invoice number and total, with three actions — **View receipt** (opens the printable receipt in the same window), **Print receipt** (prints silently in the background, no new window), and **New sale** (dismisses the dialog and starts the next order — the cart is already cleared behind it).

## Multi-store & terminals

If more than one store location is set up (Admin → Stores), a store switcher appears in the topbar for Admin/Manager. Everything — stock levels, sales, the dashboard, reports — is scoped to whichever store is currently selected. Cashiers are locked to their assigned store and don't see the switcher.

**Terminals** (Admin → Terminals) are optional named registers within a store (e.g. "Register 1", "Register 2"). If a store has none configured, shift-opening skips the terminal picker entirely — this is fully backward-compatible for a single-till store.

## Receipt printing hardware

The receipt template is formatted for **58mm thermal paper** (`resources/views/sales/receipt.blade.php`) — if a different paper width is used, the CSS `@page`/`max-width` values in that file need updating to match.

Generic USB thermal receipt printers (the kind sold as "POS-58"/"5890K" style, regardless of the brand printed on the case) commonly use a Winbond/Nuvoton USB-to-printer chip that Windows partially recognizes on its own. If a printer isn't showing up as usable:

1. Check Device Manager for a new entry under "Ports (COM & LPT)" or "USB Printing Support" when it's plugged in and powered on.
2. Use Windows' **Add a Printer** wizard → "The printer that I want isn't listed" → "Add a local printer with manual settings" → pick the port that appeared → manufacturer **Generic** → **Generic / Text Only**.
3. If print jobs get stuck in "Error, Printing, In queue" — check the printer physically first (paper loaded, cover closed, status light steady not blinking), then clear the stuck job and restart the print spooler:
   ```powershell
   Stop-Service -Name Spooler -Force
   Remove-Item -Path "$env:SystemRoot\System32\spool\PRINTERS\*" -Force
   Start-Service -Name Spooler
   ```

Barcode scanners of the common USB variety need no driver at all — they present to Windows as a keyboard, "typing" the scanned code followed by Enter into whatever field has focus (this is why the POS screen's barcode field exists as a dedicated focus target, and F8 jumps to it).

## Store branding (name, logo, receipt details)

**Settings → General** controls the store name, logo, address, and phone shown on receipts, the browser tab title, the sidebar, and the login page footer — all driven from the same database-backed value (`Setting::get('store_name', ...)`), so changing it in one place updates everywhere. This is separate from `.env`'s `APP_NAME`, which is only a fallback used if no store name has been set yet.

## Notifications & print queue

The bell icon in the topbar shows low-stock alerts (checked hourly, per store) and stock-transfer-awaiting-receipt notifications. The printer icon shows recent print jobs and lets you re-open the print dialog for any of them — labeled honestly as "print dialog opened," not "printed successfully," since a browser has no way to confirm a physical printer actually produced output.

## Offline behavior

The POS screen keeps working without a network connection — sales made offline are queued locally and sync automatically once the connection returns (a banner at the top of the POS screen shows the queued count). This is powered by a service worker; if you're testing offline behavior, be aware that simulating "offline" through browser dev tools can behave differently from genuinely losing network access, particularly around whether a previously-installed app reliably reloads from cache after being fully closed and reopened while offline — this was flagged during development as not fully verified and is worth a manual check if offline resilience after a full app restart matters for your use case.
