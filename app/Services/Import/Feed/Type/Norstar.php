<?php


namespace App\Services\Import\Feed\Type;


use App\Repositories\Feed\FeedApiUploadsRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class Norstar
 *
 * Feed uploader for Norstar-specific format
 *
 * @package App\Services\Import\Feed\Type
 */
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
            if (isset($transaction['action']) && isset($transaction['parameters']) && is_array($transaction['parameters'])) {
                switch ($transaction['action']) {

                    // add inventory unit
                    case 'addInventory':
                        Log::info("Norstar Import: adding inventory", [
                            'inventory' => $transaction['parameters']
                        ]);
                        $this->repository->createOrUpdate([
                            'code' => $this->feedCode(),
                            'key' => $transaction['parameters']['vin'],
                            'type' => 'inventory',
                            'data' => json_encode($transaction['parameters']),
                        ], $this->feedCode(), $transaction['parameters']['vin']);
                        $completed++;
                        break;

                    // add dealer
                    case 'addDealer':
                        Log::info("Norstar Import: adding dealer", [
                            'dealer' => $transaction['parameters']
                        ]);
                        $this->repository->createOrUpdate([
                            'code' => $this->feedCode(),
                            'key' => $transaction['parameters']['dealerId'],
                            'type' => 'dealer',
                            'data' => json_encode($transaction['parameters']),
                        ], $this->feedCode(), $transaction['parameters']['dealerId']);
                        $completed++;
                        break;

                    default:
                        Log::warning("Norstar Import: invalid action {$transaction['action']}");
                }
            } else {
                Log::warning("Norstar importer uploader error: transaction row not valid");
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
