<?php

namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use App\Exceptions\Ecommerce\TextrailException;
use App\Exceptions\NotImplementedException;

class TextrailMagento implements DataProviderInterface,
                                 TextrailWithCheckoutInterface,
                                 TextrailPartsInterface
{
    private const TEXTRAIL_CATEGORY_URL = 'rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'media/catalog/product';
    private const TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL = '/placeholder/default/TexTrail-LogoVertical_4_3.png';

    const VIEW_ID = 'trailer_central_t1_sv';
    const GUEST_CART_URL = 'rest/:view/V1/guest-carts';
    const GUEST_CART_POPULATE_URL = 'rest/:view/V1/guest-carts/:cartId/items';
    const GUEST_SHIPPING_COST_URL = 'rest/:view/V1/guest-carts/:cartId/estimate-shipping-methods';
    const GUEST_CART_CREATE_ORDER = '/rest/:view/V1/guest-carts/:cartId/order';
    const GUEST_CART_AVAILABLE_PAYMENT_METHODS = '/rest/:view/V1/guest-carts/:cartId/payment-methods';
    const GUEST_CART_ADD_SHIPPING_INFO = '/rest/:view/V1/guest-carts/:cartId/shipping-information';

    /** @var string */
    private $apiUrl;

    /** @var GuzzleHttpClient */
    private $httpClient;

    /** @var string */
    private $token;

    /** @var boolean */
    private $isGuestCheckout;

    public function __construct()
    {
        $this->apiUrl = config('ecommerce.textrail.api_url');
        $this->httpClient = new GuzzleHttpClient(['base_uri' => $this->apiUrl]);
        $this->isGuestCheckout = config('ecommerce.textrail.is_guest_checkout');
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-customer/tag/customers#operation/customerAccountManagementV1CreateAccountPost
     *
     * @param array $params
     * @return array{id: int, username: string, password: string}
     */
    public function createCustomer(array $params): array
    {
        $response = $this->httpClient->post('/rest/V1/customers', [
            'json' => $params,
            'http_errors' => false
        ]);

        return [
            'username' => $params['customer']['email'],
            'password' => $params['password'],
            'id' => $response['id']
        ];
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-customer/tag/integrationcustomertoken
     *
     * @param array{username: string, password: string} $credentials
     * @return string the token for the provided customer credentials
     */
    public function generateAccessToken(array $credentials)
    {
        $response = $this->httpClient->post('rest/V1/integration/customer/token', [
            'json' => $credentials
        ]);

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new \LogicException('Token generation failed.');
    }

    /**
     * @param array $params
     * @param int $quoteId a cart id
     * @return array{item_id: int, sku: string, qty: int, name: string, price: float, product_type: string, quote_id: int}
     * @throws \App\Exceptions\Ecommerce\TextrailException if guzzle has failed during add item.
     */
    public function addItemToCart(array $params, int $quoteId): array
    {
        try {
            $createdItems = [];

            foreach ($params as $item) {
                $response = $this->httpClient->post('rest/V1/carts/mine/items', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token
                    ],
                    'json' => [
                        'cartItem' => [
                            'sku' => $item['sku'],
                            'qty' => $item['qty'],
                        ]
                    ]
                ]);

                $createdItems[] = json_decode($response->getBody()->getContents(), true);
            }

            return $createdItems;
        } catch (ClientException $exception) {
            throw new TextrailException("Method: addItemToCart, Details: " . $exception->getMessage());
        }
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-guest/tag/guest-cartscartIditems#operation/quoteGuestCartItemRepositoryV1SavePost
     *
     * @param array $items
     * @param string $quoteId a cart id
     * @return array{item_id: int, sku: string, qty: int, name: string, price: float, product_type: string, quote_id: int}
     * @throws \App\Exceptions\Ecommerce\TextrailException if guzzle has failed during add item.
     */
    public function addItemToGuestCart(array $items, string $quoteId): array
    {
        try {
            $createdItems = [];

            foreach ($items as $item) {
                $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_CART_POPULATE_URL, $quoteId), [
                    'json' => [
                        'cartItem' => [
                            'sku' => $item['sku'],
                            'qty' => $item['qty']
                        ]
                    ]
                ]);

                $createdItems[] = json_decode($response->getBody()->getContents(), true);
            }

            return $createdItems;
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
     * @see https://magento.redoc.ly/2.3.7-guest/tag/guest-carts#operation/quoteGuestCartManagementV1CreateEmptyCartPost
     *
     * @return string a guest cart id which should be recorded somewhere to be able creating an order from it.
     * @throws \App\Exceptions\Ecommerce\TextrailException wrong status or guzzle exceptions handling
     */
    public function createGuestCart(): string
    {
        try {
            $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_CART_URL), []);

            $quoteId = json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
                return $quoteId;
            }

            throw new TextrailException("Method: crateGuestCart, Details: Api returns invalid status code for cart, Code: " . $response->getStatusCode() . ", Message: " . $quoteId);
        } catch (ClientException $exception) {
            throw new TextrailException("Method: createGuestCart, Details: " . $exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @return array{cost: float, tax: float, cart_id: string, customer_id: ?int, method_code: string, carrier_code: string}
     */
    public function estimateShippingCost(array $params): array
    {
        // for now we only will support guest cart
        // return $this->isGuestCheckout ? $this->estimateGuestShipping($params) : $this->estimateCustomerShippingCost($params);
        return $this->estimateGuestShipping($params);
    }

    /**
     * @param array $params
     * @throws \App\Exceptions\Ecommerce\TextrailException handles guzzle exception during estimation.
     * @return array{cost: float, tax: float, cart_id: string, customer_id: ?int, method_code: string, carrier_code: string}
     */
    public function estimateGuestShipping(array $params): array
    {
        try {
            $shipping_details = $params['shipping_details'];
            $items = $params['items'];

            // We are using cart_id as quote_id for guest checkouts, and turning it into an order at the ending of payment process
            $quoteId = $this->createGuestCart();

            $cartItems = $this->addItemToGuestCart($items, $quoteId);

            $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_SHIPPING_COST_URL, $quoteId), [
                'json' => $shipping_details
            ]);

            $shippingInfo = json_decode($response->getBody()->getContents(), true);

            $shippingInfo = array_filter($shippingInfo, function ($costItem) {
                return strtolower($costItem['method_code']) !== strtolower('freeshipping');
            });

            $shippingInfo = reset($shippingInfo);

            $this->addShippingInformationToGuestCart(
                $quoteId,
                [
                    'shipping_carrier_code' => $shippingInfo['carrier_code'],
                    'shipping_method_code' => $shippingInfo['method_code'],
                    'shipping_address' => $shipping_details['address'],
                    'billing_address' => $shipping_details['address'],
                ]
            );

            $tax = ($shippingInfo['price_incl_tax'] - $shippingInfo['price_excl_tax'] < 0) ? 0 : $shippingInfo['price_incl_tax'] - $shippingInfo['price_excl_tax'];

            return [
                'cost' => $shippingInfo['price_incl_tax'],
                'tax' => $tax,
                'cart_id' => $quoteId,
                'customer_id' => null,
                'items' => $cartItems,
                'method_code' => $shippingInfo['method_code'],
                'carrier_code' => $shippingInfo['carrier_code']
            ];
        } catch (ClientException $exception) {
            throw new TextrailException("Method: estimateGuestShipping, Details: " . $exception->getMessage());
        }
    }

    /**
     * @param array $params
     * @throws \App\Exceptions\Ecommerce\TextrailException handles guzzle exception during estimation.
     * @return array{cost: float, tax: float, cart_id: string, customer_id: ?int, method_code: string, carrier_code: string}
     */
    public function estimateCustomerShippingCost(array $params): array
    {
        try {
            $customer_details = $params['customer_details'];
            $shipping_details = $params['shipping_details'];
            $items = $params['items'];

            $customer = $this->createCustomer($customer_details);
            $this->token = $this->generateAccessToken(['password' => $customer['password'], 'username' => $customer['username']]);
            // We are using cart_id as quote_id for guest checkouts, and turning it into an order at the ending of payment process
            $quoteId = $this->createQuote();

            $cartItems = $this->addItemToCart($items, $quoteId);

            $response = $this->httpClient->post('rest/V1/carts/mine/estimate-shipping-methods', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'json' => $shipping_details
            ]);

            $shippingInfo = json_decode($response->getBody()->getContents(), true);

            $shippingInfo = array_filter($shippingInfo, function ($costItem) {
                return strtolower($costItem['method_code']) === strtolower('shipping');
            });

            $shippingInfo = reset($shippingInfo);

            //@todo: add shipping information to cart

            $tax = ($shippingInfo['price_incl_tax'] - $shippingInfo['price_excl_tax'] < 0) ? 0 : $shippingInfo['price_incl_tax'] - $shippingInfo['price_excl_tax'];

            return [
                'cost' => $shippingInfo['price_incl_tax'],
                'tax' => $tax,
                'cart_id' => (string)$quoteId,
                'customer_id' => $customer['id'],
                'items' => $cartItems,
                'method_code' => $shippingInfo['method_code'],
                'carrier_code' => $shippingInfo['carrier_code']
            ];
        } catch (ClientException $exception) {
            throw new TextrailException("Method: estimateCustomerShippingCost, Details: " . $exception->getMessage());
        }
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-customer/tag/cartsmine#operation/quoteCartManagementV1CreateEmptyCartForCustomerPost
     *
     * @return int
     */
    public function createQuote(): int
    {
        $response = $this->httpClient->post('rest/V1/carts/mine', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token
            ]
        ]);

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return $response->getBody()->getContents();
        }

        throw new \LogicException('Quote generation failed.');
    }

    /**
     * @return array<TextrailPartDTO>
     */
    public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array
    {
      $totalParts = $this->getTextrailTotalPartsCount();
      $url = 'rest/' . Config::get('ecommerce.textrail')['store'] . '/V1/products';

      $Allparts = [];

      for ($i=0; $i < $totalParts; $i+=$pageSize) {

        $queryParts = [
          'searchCriteria[page_size]' => $pageSize,
          'searchCriteria[currentPage]' => $currentPage,
          'searchCriteria[filter_groups][0][filters][0][field]' => 'website_id',
          'searchCriteria[filter_groups][0][filters][0][value]' => 10,
          'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq'

        ];

        $parts = json_decode($this->httpClient->get($url, ['headers' => $this->getHeaders(), 'query' => $queryParts])->getBody()->getContents());

        foreach ($parts->items as $item) {

          $description = '';
          $category_id;
          $manufacturer_id;
          $brand_id = '';
          $images = [];

          foreach ($item->custom_attributes as $custom_attribute) {

            if ($custom_attribute->attribute_code == 'short_description') {
              $description = $custom_attribute->value;
            }

            if ($custom_attribute->attribute_code == 'category_ids') {
              $category_id = $custom_attribute->value[0];
            }

            if ($custom_attribute->attribute_code == 'manufacturer' && ($custom_attribute->value  > 0)) {
              $manufacturer_id = $custom_attribute->value;
            }

            if ($custom_attribute->attribute_code == 'brand_name' && ($custom_attribute->value  > 0)) {
              $brand_id = $custom_attribute->value;
            }

          }

          foreach ($item->media_gallery_entries as $img) {
            array_push($images, ['file' => $img->file, 'position' => $img->position]);
          }

          $dtoTextrail = TextrailPartDTO::from([
            'id' => $item->id,
            'sku' => $item->sku,
            'title' => $item->name,
            'price' => $item->price,
            'weight' => isset($item->weight) ? $item->weight : '',
            'description' => $description,
            'category_id' => isset($category_id) ? $category_id : '',
            'manufacturer_id' =>  isset($manufacturer_id) ? $manufacturer_id : '',
            'brand_id' => $brand_id,
            'show_on_website' => 1,
            'images' => $images
          ]);
          array_push($Allparts, $dtoTextrail);
        }
        $currentPage++;
      }

      return $Allparts;
    }

    public function getTextrailCategory(int $categoryId): object
    {
      return json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $categoryId, ['headers' => $this->getHeaders()])->getBody()->getContents());
    }

    public function getTextrailManufacturers(): array
    {
      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL, ['headers' => $this->getHeaders()])->getBody()->getContents());
    }

    public function getTextrailBrands(): array
    {
      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL, ['headers' => $this->getHeaders()])->getBody()->getContents());
    }

    /**
     * @return null|array{imageData: array, fileName: string}
     */
    public function getTextrailImage(array $img): ?array
    {
      $img_url = $this->getTextrailImagesBaseUrl() . $img['file'];
      $checkFile = get_headers($img_url);

      if ($checkFile[0] == "HTTP/1.1 200 OK") {
        $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        $explodedImage = explode('/', $img['file']);
        $fileName = $explodedImage[count($explodedImage) - 1];

        return ['imageData' => $imageData, 'fileName' => $fileName];
      }

      return null;
    }

    /**
     * @return null|array{imageData: array, fileName: string}
     */
    public function getTextrailPlaceholderImage(): ?array
    {
      $img_url = $this->getTextrailImagesBaseUrl() . self::TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL;
      
      $checkFile = get_headers($img_url);

      if ($checkFile[0] == "HTTP/1.1 200 OK") {
        $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        $explodedImage = explode('/', self::TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL);
        $fileName = $explodedImage[count($explodedImage) - 1];

        return ['imageData' => $imageData, 'fileName' => $fileName];
      }

      return null;
    }

    protected function getTextrailImagesBaseUrl(): string
    {
      return $this->apiUrl . self::TEXTRAIL_ATTRIBUTES_MEDIA_URL;
    }

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int
    {
      $query = [
        'searchCriteria[page_size]' => $pageSize,
        'searchCriteria[currentPage]' => $currentPage,
        'searchCriteria[filter_groups][0][filters][0][field]' => 'website_id',
        'searchCriteria[filter_groups][0][filters][0][value]' => 10,
        'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq'
      ];

      $url = 'rest/' . Config::get('ecommerce.textrail')['store'] . '/V1/products';

      $getPart = json_decode($this->httpClient->get($url, ['headers' => $this->getHeaders(), 'query' => $query])->getBody()->getContents());

      return $getPart->total_count;
    }

    /**
     * @return array{"Content-Type": string, "Authorization": string}
     */
    private function getHeaders(): array
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];

      return [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-guest/tag/guest-cartscartIdorder
     *
     * @param string $cartId
     * @return string order id
     * @throws \App\Exceptions\Ecommerce\TextrailException when some error occurs on the Magento side
     */
    public function createOrderFromGuestCart(string $cartId, string $poNumber): string
    {
        $url = $this->generateUrlWithCartAndView(self::GUEST_CART_CREATE_ORDER, $cartId);

        $response = $this->httpClient->put($url, [
                'json' => [
                    'paymentMethod' => [
                        'method' => config('ecommerce.textrail.payment_method'),
                        'po_number' => $poNumber
                    ]
                ]
            ]
        );

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new TextrailException('Order creation for session cart has failed.');
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-guest/tag/guest-cartscartIdshipping-information#operation/checkoutGuestShippingInformationManagementV1SaveAddressInformationPost
     *
     * @param string $cartId
     * @param array{shipping_carrier_code: string, shipping_method_code: string, shipping_address: array, billing_address: array} $shippingInformation
     */
    public function addShippingInformationToGuestCart(string $cartId, array $shippingInformation): void
    {
        $url = $this->generateUrlWithCartAndView(self::GUEST_CART_ADD_SHIPPING_INFO , $cartId);

        $response = $this->httpClient->post($url, [
                'json' => ['addressInformation' => $shippingInformation]
            ]
        );

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return;
        }

        throw new TextrailException('Order creation for session cart has failed.');
    }

    public function createOrderFromCart(string $cartId): string
    {
        throw new NotImplementedException;
    }
}
