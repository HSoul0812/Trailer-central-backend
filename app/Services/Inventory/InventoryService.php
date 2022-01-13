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
    public function __construct(public GuzzleHttpClient $httpClient)
    {
    }

    /**
     * @param int $id the id of the inventory
     */
    public function show(int $id): TcApiResponseInventory
    {
        $url = config('services.trailercentral.api') . 'inventory/' . $id . '?include=features';
        $inventory = $this->handleHttpRequest('GET', $url);

        return TcApiResponseInventory::fromData($inventory['data']);
    }

    /**
     * @param string $method
     * @param string $url
     *
     * @return array
     */
    #[ArrayShape([
        'data' => [[
            'title'   => 'string',
            'address' => [
                'id'               => 'int',
                'payload_capacity' => 'float',
                'url'              => 'string',
                'description'      => 'string',
                'gvwr'             => 'float',
                'weight'           => 'float',
                'width'            => 'float',
                'height'           => 'float',
                'length'           => 'float',
                'manufacturer'     => 'string',
                'created_at'       => 'string',
                'price'            => 'float',
                'sales_price'      => 'float',
                'title'            => 'string',
            ],
            'images' => [
                'image_id'     => 'int',
                'is_default'   => 'int',
                'is_secondary' => 'int',
                'position'     => 'int',
                'url'          => 'string',
            ],
            'dealer' => [
                'id'            => 'int',
                'identifier'    => 'string',
                'created_at'    => 'string',
                'name'          => 'string',
                'email'         => 'string',
                'profile_image' => 'string',
            ],
            'features' => [
                'feature_list_id' => 'int',
                'value'           => 'string',
                'feature_name'    => 'string',
            ],
            'dealer_location' => [
                'id'         => 'int',
                'identifier' => 'string',
                'contact'    => 'string',
                'name'       => 'string',
                'website'    => 'string',
                'phone'      => 'string',
                'fax'        => 'string',
                'address'    => 'string',
                'city'       => 'string',
                'county'     => 'string',
                'region'     => 'string',
                'postal'     => 'string',
                'postalcode' => 'string',
                'country'    => 'string',
                'federal_id' => 'string',
                'sales_tax'  => 'array',
            ],
        ]],
    ])]
    private function handleHttpRequest(string $method, string $url): array
    {
        try {
            $response = $this->httpClient->request($method, $url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            \Log::info('Exception was thrown while calling TrailerCentral API.');
            \Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}
