@props(['amount'])
<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>{{ \App\Models\Setting::get('currency_symbol', '₦') }}{{ number_format((float) $amount, 2) }}</span>
