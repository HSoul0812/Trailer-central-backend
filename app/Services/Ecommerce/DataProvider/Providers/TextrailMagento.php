<?php
namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use http\Exception\InvalidArgumentException;

class TextrailMagento implements DataProviderInterface, TextrailMagentoInterface
{
    /** @var string */
    private $apiUrl;

    /** @var GuzzleHttpClient */
    private $httpClient;

    /** @var string */
    private $token;

    /** @var int */
    private $quoteId;

    /**
     * TextrailMagento constructor.
     * @param string $apiUrl
     */
    public function __construct()
    {
        $this->apiUrl = env("TEXTRAIL_API_URL", 'https://mcstaging.textrail.com/');
        $this->httpClient = new GuzzleHttpClient(['base_uri' => $this->apiUrl, 'timeout' => 5.0]);
    }

    public function createCustomer(array $params): array
    {
        $this->httpClient->post('/rest/V1/customers', [
            'json' => $params,
            'http_errors' => false
        ]);

        return [
            'username' => $params['customer']['email'],
            'password' => $params['password']
        ];
    }

    public function generateAccessToken(array $credentials)
    {
        $response = $this->httpClient->post('rest/V1/integration/customer/token', [
            'json' => $credentials
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new \LogicException('Token generation failed.');
    }

    public function addItemToCart(array $params)
    {
        foreach ($params as $item) {
            $this->httpClient->post('rest/V1/carts/mine/items', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ],
                'json' => [
                   'cartItem' => [
                       'sku' => $item['sku'],
                       'qty' => $item['qty'],
                       'quote_id' => $this->quoteId
                   ]
                ]
            ]);
        }
    }

    public function estimateShippingCost(array $params)
    {
        $customer_details = $params['customer_details'];
        $shipping_details = $params['shipping_details'];
        $items = $params['items'];

        $credentials = $this->createCustomer($customer_details);
        $this->token = $this->generateAccessToken($credentials);
        $this->quoteId = $this->createQuote();

        $this->addItemToCart($items);

        $response = $this->httpClient->post('rest/V1/carts/mine/estimate-shipping-methods', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'json' => $shipping_details
        ]);

        $costs = json_decode($response->getBody()->getContents(), true);

        $costs = array_filter($costs, function ($costItem) {
             return strtolower($costItem['method_code']) === strtolower('shipping');
        });

        $costs = reset($costs);

        return [
            'cost' => $costs['price_incl_tax']
        ];
    }

    public function createQuote(): int
    {
        $response = $this->httpClient->post('rest/V1/carts/mine', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token
            ]
        ]);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            return $response->getBody()->getContents();
        }

        throw new \LogicException('Quote generation failed.');
    }
}