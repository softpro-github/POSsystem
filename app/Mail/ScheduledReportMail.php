<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $reportName,
        public string $pdfContent,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Scheduled Report: '.$this->reportName);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.scheduled-report',
            with: ['reportName' => $this->reportName],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, str($this->reportName)->slug().'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
