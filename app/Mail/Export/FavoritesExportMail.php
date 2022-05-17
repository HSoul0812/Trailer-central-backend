<?php

namespace App\Mail\Export;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FavoritesExportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    private $customerCsv;

    /**
     * @var string
     */
    private $inventoryCsv;

    /**
     * @var array
     */
    private $recipients;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $recipients, string $customerCsv, string $inventoryCsv)
    {
        $this->customerCsv = $customerCsv;
        $this->inventoryCsv = $inventoryCsv;
        $this->recipients = $recipients;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address', 'noreply@trailercentral.com');
        $name = config('mail.from.name', 'Trailer Central');

        return $this->from($from, $name)
            ->to($this->recipients)
            ->subject('Inventory Favorites Data Export')
            ->view('emails.export.inventory-favorites')
            ->attachData($this->customerCsv, 'customer-data.csv', [
                'mime' => 'text/csv',
            ])->attachData($this->inventoryCsv, 'inventory-data.csv', [
                'mime' => 'text/csv',
            ]);
    }
}
