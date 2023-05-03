<?php

namespace App\Mail\SubscribeEmailSearch;

use App\DTOs\SubscribeEmailSearch\SubscribeEmailSearchDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscribeEmailSearchMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\DTOS\SubscribeEmailSearch\SubscribeEmailSearchDTO
     */
    protected $subscribeEmailSearch;

    /**
     * Create a new message instance.
     */
    public function __construct(SubscribeEmailSearchDTO $subscribeEmailSearch)
    {
        $this->subject = $subscribeEmailSearch->subject;
        $this->url = $subscribeEmailSearch->url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $build = $this->from('noreply@trailercentral.com', 'noreply')->view('emails.subscribeEmailSearch');
        $build->with(['url' => $this->url]);

        return $build;
    }
}
