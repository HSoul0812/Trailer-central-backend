<?php


namespace App\Jobs\Import\Feed;


use App\Jobs\Job;
use App\Services\Import\Feed\DealerFeedUploaderService;
use Illuminate\Support\Facades\Log;

class DealerFeedImporterJob extends Job
{
    /**
     * Tha payload data for the feed
     *
     * @var string
     */
    private $data;

    /**
     * The code that corresponds to an importer that will process the data
     *
     * @var string
     */
    private $code;

    /**
     * The service that will process the data
     * @var DealerFeedUploaderService
     */
    private $feedUploader;

    public function __construct($data, string $code, DealerFeedUploaderService $feedUploader)
    {
        $this->data = $data;
        $this->code = $code;
        $this->feedUploader = $feedUploader;
    }

    public function handle()
    {
        Log::info('Starting DealerFeedImporterJob', ['code' => $this->code]);
        $this->feedUploader->run($this->data, $this->code);
    }

}
