<?php

namespace App\Mail\SubscribeEmailSearch;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\DTOs\SubscribeEmailSearch\SubscribeEmailSearchDTO;

class SubscribeEmailSearchMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\DTOS\SubscribeEmailSearch\SubscribeEmailSearchDTO
     */
    protected $subscribeEmailSearch;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SubscribeEmailSearchDTO $subscribeEmailSearch)
    {
        $this->subject = $subscribeEmailSearch->subject;
        $this->url  = $subscribeEmailSearch->url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $build = $this->from('noreply@trailercentral.com', 'noreply')->view('emails.subscribeEmailSearch');
        $build->with(['url'=> $this->url]);

        return $build;
    }
}
