<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receipt {{ $sale->invoice_number }}</title>
    @vite(['resources/js/receipt.js'])
    <style>
        body { font-family: 'Courier New', monospace; font-size: 13px; color: #111; max-width: 320px; margin: 20px auto; }
        .center { text-align: center; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 0; vertical-align: top; }
        hr { border: none; border-top: 1px dashed #999; margin: 8px 0; }
        .no-print { text-align: center; margin-top: 16px; }
        #barcode { max-width: 100%; height: auto; display: block; margin: 0 auto; }
        @media print {
            /* 58mm thermal paper — printable area is narrower than the full roll
               width (~5mm is unprintable on each edge on most 58mm heads), so the
               page is sized to the full 58mm but content is capped at 48mm and
               auto-centered within it to avoid edge clipping. */
            @page { size: 58mm auto; margin: 0; }
            .no-print { display: none; }
            body { font-size: 11px; margin: 0 auto; max-width: 48mm; }
        }
    </style>
</head>
<body>
    <div class="center">
        @php $logoPath = \App\Models\Setting::get('store_logo'); @endphp
        @if ($logoPath)
            <img src="{{ Storage::url($logoPath) }}" alt="Logo" style="max-height: 60px; max-width: 100%;"><br>
        @endif
        <strong>{{ \App\Models\Setting::get('store_name', config('app.name')) }}</strong><br>
        {{ \App\Models\Setting::get('store_address', '') }}<br>
        {{ \App\Models\Setting::get('store_phone', '') }}
    </div>
    <hr>
    <div>
        Invoice: {{ $sale->invoice_number }}<br>
        Date: {{ $sale->sold_at?->format('Y-m-d H:i') }}<br>
        Cashier: {{ $sale->user->name }}<br>
        Customer: {{ $sale->customer?->name ?? 'Walk-in' }}
    </div>
    <hr>
    <table>
        <thead>
            <tr>
                <th style="text-align:left">Item</th>
                <th class="right">Qty</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>
                        {{ $item->product->name }}
                        @if ($item->productSerial)
                            <br><small>SN: {{ $item->productSerial->imei_serial }}</small>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right"><x-money :amount="$item->subtotal" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table>
        <tr><td>Subtotal</td><td class="right"><x-money :amount="$sale->subtotal" /></td></tr>
        <tr><td>Discount</td><td class="right"><x-money :amount="$sale->discount_amount" /></td></tr>
        <tr><td>Tax</td><td class="right"><x-money :amount="$sale->tax_amount" /></td></tr>
        <tr><td><strong>Total</strong></td><td class="right"><strong><x-money :amount="$sale->total_amount" /></strong></td></tr>
        <tr><td>Paid</td><td class="right"><x-money :amount="$sale->amount_paid" /></td></tr>
        <tr><td>Change</td><td class="right"><x-money :amount="$sale->change_due" /></td></tr>
    </table>
    <hr>
    @foreach ($sale->payments as $payment)
        <div>{{ ucfirst($payment->method) }}: <x-money :amount="$payment->amount" /></div>
    @endforeach
    <hr>
    <div class="center">
        <svg id="barcode" data-value="{{ $sale->invoice_number }}"></svg>
    </div>
    <hr>
    <div class="center">
        {{ \App\Models\Setting::get('receipt_footer', 'Thank you for your patronage!') }}
    </div>

    <div class="no-print">
        <button onclick="printReceipt()">Print Receipt</button>
        @auth
            <button onclick="window.location.href = '{{ route('pos.index') }}'">Back to POS</button>
        @endauth
    </div>

    <script>
        async function printReceipt() {
            let jobId = null;
            try {
                const res = await fetch('{{ route('print-jobs.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        type: 'receipt',
                        sale_id: {{ $sale->id }},
                        reference: '{{ $sale->invoice_number }}',
                    }),
                });
                jobId = (await res.json()).id;
            } catch (e) { /* logging the print job is best-effort, never blocks printing */ }

            window.print();

            if (jobId) {
                window.addEventListener('afterprint', () => {
                    fetch(`/print-jobs/${jobId}/closed`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    });
                }, { once: true });
            }
        }

        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            printReceipt();
        }
    </script>
</body>
</html>
