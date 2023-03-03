<?php

namespace App\Services\Import\Feed;

use Illuminate\Support\Facades\Log;
use App\Repositories\Feed\FeedApiUploadsRepository;

/**
 * Class FactoryUpload
 *
 * Feed uploader for inventories
 *
 * @package App\Services\Import\Feed
 */
class FactoryUpload
{
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
     * @param $data // comes from the `rawData` field
     * @throws \Exception
     */
    public function run($data)
    {
        // $data is a string, decode to json
        $json = is_string($data) ? json_decode($data) : $data;

        // Check if transactions array is not empty
        if (empty($json['transactions'])) {
            throw new \Exception('transactions invalid or not found in rawData');
        }

        $completed = 0;
        foreach ($json['transactions'] as $transaction) {
            if (isset($transaction['action']) && isset($transaction['parameters']) && is_array($transaction['parameters'])) {
                $inventory = array_change_key_case($transaction['parameters']);

                // Validate vin key real name and assign it to $vin
                if (!empty($inventory['vin'])) {
                    $vin = $inventory['vin'];
                } else if (!empty($inventory['vin_no'])) {
                    $vin = $inventory['vin_no'];
                } else {
                    // Impossible to get here but an extra validation won't hurt.
                    continue;
                }

                switch ($transaction['action']) {
                    // Add inventory unit
                    case 'addInventory':
                        Log::info("{$json['code']} Import: adding inventory with VIN: " . $vin);
                        $this->repository->createOrUpdate([
                            'code' => $json['code'],
                            'key' => $vin,
                            'type' => 'inventory',
                            'data' => json_encode($inventory),
                        ], $json['code'], $vin);
                        $completed++;
                        break;

                    // Add dealer (Leaving this here if needed in the future)
                    case 'addDealer':
                        Log::info("{$json['code']} Import: adding dealer", [
                            'dealer' => $transaction['parameters']
                        ]);
                        $this->repository->createOrUpdate([
                            'code' => $json['code'],
                            'key' => $transaction['parameters']['dealerId'],
                            'type' => 'dealer',
                            'data' => json_encode($transaction['parameters']),
                        ], $json['code'], $transaction['parameters']['dealerId']);
                        $completed++;
                        break;

                    default:
                        Log::warning("{$json['code']} Import: invalid action {$transaction['action']}");
                }
            } else {
                Log::warning("{$json['code']} importer uploader error: transaction row not valid");
            }
        }
    }
}
