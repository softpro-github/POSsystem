@component('mail::message')
# {{ $reportName }}

Your scheduled report is attached as a PDF.

Thanks,<br>
{{ \App\Models\Setting::get('store_name', config('app.name')) }}
@endcomponent
