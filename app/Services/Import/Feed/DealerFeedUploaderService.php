<?php


namespace App\Services\Import\Feed;


use Illuminate\Support\Facades\Log;

/**
 * Class DealerFeedUploaderService
 *
 * Service that processes uploads from users; uploaded data is stored to be picked up by a
 *   collector in a separate process
 *
 * @package App\Services\Import\Feed
 */
class DealerFeedUploaderService
{

    /**
     * @var ImporterFactory
     */
    private $factory;

    public function __construct(ImporterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Run the service with the data
     * @param mixed $data the source data
     * @param string $code the name/code of the feed; table feeds.code
     * @return mixed
     * @throws \Exception
     */
    public function run($data, $code)
    {
        // make the importer
        $importer = $this->factory->build($code);

        // run the importer with the data
        return $importer->run($data);
    }

}
