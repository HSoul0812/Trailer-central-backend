<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\DTOs\Inventory\TcApiResponseInventory;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $inventory = $this->handleHttpRequest('GET', $url);

        return TcApiResponseInventory::fromData($inventory['data']);
    }

    private function handleHttpRequest(string $method, string $url): array
    {
        try {
            $response = $this->httpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling here API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(500, $e->getMessage());
        }
    }
}
