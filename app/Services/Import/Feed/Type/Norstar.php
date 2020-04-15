<?php


namespace App\Services\Import\Feed\Type;


use App\Repositories\Feed\FeedApiUploadsRepository;

class Norstar implements FeedImporterInterface
{
    const FEED_CODE = 'norstar';

    /**
     * @var FeedApiUploadsRepository
     */
    private $repository;

    public function __construct(FeedApiUploadsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Just parse the data, then for each addInventory operation, add it to the import table
     * @param string $data comes from the `rawData` field
     * @throws \Exception
     */
    public function run($data)
    {
        // $data is a string, decode to json
        $json = is_string($data)?  json_decode($data): $data;

        // check if transactions exist
        if (empty($json['transactions'])) {
            throw new \Exception('transactions invalid or not found in rawData');
        }

        // loop through transactions
        $completed = 0;
        foreach ($json['transactions'] as $transaction) {
            // if transaction action type is add
            if (isset($transaction['parameters'])) {
                $this->repository->create([
                    'code' => $this->feedCode(),
                    'type' => 'add',
                    'data' => json_encode($transaction['parameters']),
                ]);
                $completed++;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function feedCode()
    {
        return self::FEED_CODE;
    }
}
