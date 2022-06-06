<?php

namespace App\Jobs\CRM\Interactions\Facebook;

use App\Jobs\Job;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Interactions\Facebook\MessageServiceInterface;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

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
        // Iniitalize Logger
        $log = Log::channel('facebook');

        // Scrape Messages
        try {
            $log->error('Handling Facebook\MessageJob for Page #' . $this->pageId);
            $messages->scrapeMessages($this->pageToken, $this->pageId);
            $log->error('Handled Facebook\MessageJob for Page #' . $this->pageId);
        } catch (\Exception $ex) {
            $log->error('Exception returned Handling Facebook\MessageJob: ' . $ex->getMessage());
        }
    }
}
