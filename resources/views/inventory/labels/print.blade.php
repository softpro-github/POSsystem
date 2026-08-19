<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Print Labels</title>
    @vite(['resources/js/labels.js'])
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #fff; color: #111; }
        .sheet { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2mm; padding: 10mm; }
        .label { border: 1px dashed #ccc; padding: 2mm; text-align: center; break-inside: avoid; }
        .label .name { font-size: 10px; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label .price { font-size: 11px; margin-top: 1mm; }
        .no-print { text-align: center; margin: 16px; }
        @media print {
            .no-print { display: none; }
            .label { border: none; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="printLabels()">Print</button>
    </div>

    <script>
        async function printLabels() {
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
                        type: 'label',
                        payload: @json($items),
                        reference: '{{ count($labels) }} labels',
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
    </script>

    <div class="sheet">
        @foreach ($labels as $product)
            <div class="label">
                <div class="name">{{ $product->name }}</div>
                <svg class="label-barcode" jsbarcode-value="{{ $product->barcode ?: $product->sku }}"></svg>
                <div class="price">₦{{ number_format((float) $product->selling_price, 2) }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
