<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td, th { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .muted { color: #777; font-size: 11px; }
    </style>
</head>
<body>
    <h1>{{ $reportName }}</h1>
    <p class="muted">
        Generated {{ now()->format('Y-m-d H:i') }}
        @if ($from && $to)
            — period {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}
        @endif
    </p>
    <table>
        @foreach ($summary as $key => $value)
            <tr>
                <td>{{ \Illuminate\Support\Str::headline(is_numeric($key) ? (string) $key : $key) }}</td>
                <td>{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
