<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Provides a logger instance according to PSR-3.
 */
class InventoryService implements InventoryServiceInterface
{
    /**
     * @var GuzzleHttpClient
     */
    private $httpClient;

    public function __construct(GuzzleHttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id)
    {
        $url = config('services.trailercentral.api') . 'inventory/' . $id . '?include=features';

        return json_decode($this->httpClient->get($url, ['http_errors' => false])->getBody()->getContents());
    }
}
