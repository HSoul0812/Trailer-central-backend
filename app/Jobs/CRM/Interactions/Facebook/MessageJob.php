<?php

namespace App\Jobs\CRM\Interactions\Facebook;

use App\Jobs\Job;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use Illuminate\Foundation\Bus\Dispatchable;

class MessageJob extends Job
{
    use Dispatchable;


    /**
     * @var AccessToken $pageToken
     */
    private $pageToken;

    /**
     * @var int $pageId
     */
    private $pageId;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AccessToken $pageToken, int $pageId)
    {
        // Set Feed Path/Integration to Process
        $this->pageToken = $pageToken;
        $this->pageId = $pageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MessageServiceInterface $messages)
    {
        // Scrape Messages
        $messages->scrapeMessages($this->pageToken, $this->pageId);
    }
}
