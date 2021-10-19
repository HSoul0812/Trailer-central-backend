<?php
namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\ClientException;

class TextrailMagento implements DataProviderInterface, TextrailGuestCheckoutInterface
{
    const VIEW_ID = 'trailer_central_t1_sv';
    const GUEST_CART_URL = 'rest/:view/V1/guest-carts';
    const GUEST_CART_POPULATE_URL = 'rest/:view/V1/guest-carts/:cartId/items';
    const GUEST_SHIPPING_COST_URL = 'rest/:view/V1/guest-carts/:cartId/estimate-shipping-methods';

    /** @var string */
    private $apiUrl;

    /** @var GuzzleHttpClient */
    private $httpClient;

    /** @var string */
    private $token;

    /** @var boolean */
    private $isGuestCheckout;

    /**
     * TextrailMagento constructor.
     * @param string $apiUrl
     */
    public function __construct()
    {
        $this->apiUrl = env("TEXTRAIL_API_URL", 'https://mcstaging.textrail.com/');
        $this->httpClient = new GuzzleHttpClient(['base_uri' => $this->apiUrl, 'timeout' => 5.0]);
        $this->isGuestCheckout = env('TEXTRAIL_GUEST_CHECKOUT', true);
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

    public function addItemToCart(array $params, int $quoteId)
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
                       'quote_id' => $quoteId
                   ]
                ]
            ]);
        }
    }

    /**
     * @param array $params
     * @param string $quoteId
     * @throws TextrailException if guzzle has failed during add item.
     */
    public function addItemToGuestCart(array $params, string $quoteId)
    {
        try {
            foreach ($params as $item) {
                $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_CART_POPULATE_URL, $quoteId), [
                    'json' => [
                        'cartItem' => [
                            'sku' => $item['sku'],
                            'qty' => $item['qty'],
                            'quote_id' => $quoteId
                        ]
                    ]
                ]);
            }
        } catch (ClientException $exception) {
            throw new TextrailException("Method: addItemToGuestCart, Details: " . $exception->getMessage());
        }
    }

    private function generateUrlWithCartAndView(string $url, string $cart = null): string
    {
        $url = str_replace(':view', self::VIEW_ID, $url);
        $url = str_replace(':cartId', $cart, $url);
        return $url;
    }

    /**
     * @return string
     * @throws TextrailException wrong status or guzzle exceptions handling
     */
    public function createGuestCart(): string
    {
        try {
            $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_CART_URL), []);

            $responseJson = json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() === Response::HTTP_OK) {
                return $responseJson;
            }

            throw new TextrailException("Method: crateGuestCart, Details: Api returns invalid status code for cart, Code: " . $response->getStatusCode() . ", Message: " . $responseJson);
        } catch (ClientException $exception) {
            throw new TextrailException("Method: createGuestCart, Details: " . $exception->getMessage());
        }
    }

    public function estimateShippingCost(array $params): array
    {
        return $this->isGuestCheckout ? $this->estimateGuestShipping($params) : $this->estimateCustomerShippingCost($params);
    }

    /**
     * @param array $params
     * @throws TextrailException handles guzzle exception during estimation.
     * @return array{"cost": float, "tax": float}
     */
    public function estimateGuestShipping(array $params): array
    {
        try {
            $shipping_details = $params['shipping_details'];
            $items = $params['items'];

            // We are using cart_id as quote_id for guest checkouts.
            $quoteId = $this->createGuestCart();

            $this->addItemToGuestCart($items, $quoteId);

            $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_SHIPPING_COST_URL, $quoteId), [
                'json' => $shipping_details
            ]);

            $costs = json_decode($response->getBody()->getContents(), true);

            $costs = array_filter($costs, function ($costItem) {
                return strtolower($costItem['method_code']) !== strtolower('freeshipping');
            });

            $costs = reset($costs);

            $tax = ($costs['price_incl_tax'] - $costs['price_excl_tax'] < 0) ? 0 : $costs['price_incl_tax'] - $costs['price_excl_tax'];

            return [
                'cost' => $costs['price_incl_tax'],
                'tax' => $tax,
            ];
        } catch (ClientException $exception) {
            throw new TextrailException("Method: estimateGuestShipping, Details: " . $exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @throws TextrailException handles guzzle exception during estimation.
     * @return array{"cost": float, "tax": float}
     */
    public function estimateCustomerShippingCost(array $params): array
    {
        try {
            $customer_details = $params['customer_details'];
            $shipping_details = $params['shipping_details'];
            $items = $params['items'];

            $credentials = $this->createCustomer($customer_details);
            $this->token = $this->generateAccessToken($credentials);
            $quoteId = $this->createQuote();

            $this->addItemToCart($items, $quoteId);

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

            $tax = ($costs['price_incl_tax'] - $costs['price_excl_tax'] < 0) ? 0 : $costs['price_incl_tax'] - $costs['price_excl_tax'];

            return [
                'cost' => $costs['price_incl_tax'],
                'tax' => $tax,
            ];
        } catch (ClientException $exception) {
            throw new TextrailException("Method: estimateCustomerShippingCost, Details: " . $exception->getMessage());
        }
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