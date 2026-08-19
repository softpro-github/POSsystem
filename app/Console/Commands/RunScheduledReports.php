<?php

namespace App\Console\Commands;

use App\Http\Controllers\Reports\ReportController;
use App\Mail\ScheduledReportMail;
use App\Models\SavedReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';

    protected $description = 'Email any saved reports that are due on their schedule.';

    public function handle(ReportController $controller): int
    {
        $due = SavedReport::whereNotNull('schedule_frequency')->get()->filter->isDue();

        if ($due->isEmpty()) {
            $this->info('No scheduled reports are due.');

            return self::SUCCESS;
        }

        foreach ($due as $saved) {
            $method = (string) Str::of($saved->report_type)->after('reports.');

            if (! method_exists($controller, $method)) {
                $this->warn("Skipping \"{$saved->name}\" — unknown report type {$saved->report_type}.");

                continue;
            }

            parse_str($saved->filters['qs'] ?? '', $queryParams);
            $request = Request::create('/', 'GET', $queryParams);

            $view = app()->call([$controller, $method], ['request' => $request]);
            $data = $view->getData();

            $pdfHtml = view('reports.pdf-summary', [
                'reportName' => $saved->name,
                'summary' => $data['summary'] ?? collect(),
                'from' => $data['from'] ?? null,
                'to' => $data['to'] ?? null,
            ])->render();

            $pdf = Pdf::loadHTML($pdfHtml);

            $recipients = collect(explode(',', $saved->recipients ?? ''))
                ->map(fn ($email) => trim($email))
                ->filter()
                ->all();

            if (! empty($recipients)) {
                Mail::to($recipients)->send(new ScheduledReportMail($saved->name, $pdf->output()));
                $this->info("Sent \"{$saved->name}\" to ".implode(', ', $recipients));
            } else {
                $this->info("\"{$saved->name}\" is due but has no recipients — marking as run without sending.");
            }

            $saved->update(['last_run_at' => now()]);
        }

        return self::SUCCESS;
    }
}
