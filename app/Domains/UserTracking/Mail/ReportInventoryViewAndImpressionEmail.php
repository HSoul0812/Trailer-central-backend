<?php

namespace App\Domains\UserTracking\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportInventoryViewAndImpressionEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private string $csvFilePath, private string $reportDate)
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->attach($this->csvFilePath)
            ->subject("Inventory view and impression report - $this->reportDate")
            ->markdown('emails.reports.inventory-view-and-impression');
    }
}
