<?php


namespace App\Jobs\Import\Feed;


use App\Jobs\Job;
use App\Services\Import\Feed\DealerFeedUploaderService;
use App\Services\Import\Feed\FactoryUpload;
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

    public function __construct($data, string $code)
    {
        $this->data = $data;
        $this->code = $code;
    }

    public function handle(FactoryUpload $factory)
    {
        Log::info('Starting DealerFeedImporterJob', ['code' => $this->code]);
        $factory->run($this->data);
    }

}
