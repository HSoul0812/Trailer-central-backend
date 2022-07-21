<?php

namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use App\Services\Ecommerce\Refund\RefundBag;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use App\Exceptions\Ecommerce\TextrailException;
use App\Exceptions\NotImplementedException;

class TextrailMagento implements DataProviderInterface,
                                 TextrailWithCheckoutInterface,
                                 TextrailPartsInterface,
                                 TextrailRefundsInterface
{
    private const TEXTRAIL_CATEGORY_URL = 'rest/V1/categories/';
    private const TEXTRAIL_CATEGORY_LIST_URL = 'rest/V1/categories/list/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_GENERIC_URL = 'rest/V1/products/attributes/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'media/catalog/product';
    private const TEXTRAIL_ATTRIBUTES_PLACEHOLDER_URL = 'placeholder/default/TexTrail-LogoVertical_4_3.png';
    private const TEXTRAIL_DUMP_STOCK_URL = 'rest/:view/V1/inventory/dump-stock-index-data/website/trailer_central_t1';

    const VIEW_ID = 'trailer_central_t1_sv';
    const GUEST_CART_URL = 'rest/:view/V1/guest-carts';
    const GUEST_CART_POPULATE_URL = 'rest/:view/V1/guest-carts/:cartId/items';
    const GUEST_SHIPPING_COST_URL = 'rest/:view/V1/guest-carts/:cartId/estimate-shipping-methods';
    const GUEST_CART_CREATE_ORDER = 'rest/:view/V1/guest-carts/:cartId/order';
    const GUEST_CART_AVAILABLE_PAYMENT_METHODS = 'rest/:view/V1/guest-carts/:cartId/payment-methods';
    const GUEST_CART_ADD_SHIPPING_INFO = 'rest/:view/V1/guest-carts/:cartId/shipping-information';

    const ORDER_GET_INFO = 'rest/:view/V1/orders/:orderId';
    const ORDER_CREATE_REFUND = 'rest/:view/V1/order/:orderId/refund';
    const ORDER_CREATE_RETURN = 'rest/:view/V1/returns';

    /** @var string */
    private $apiUrl;

    /** @var GuzzleHttpClient */
    private $httpClient;

    /** @var string */
    private $token;

    /** @var boolean */
    private $isGuestCheckout;

    /** @var object */
    private $allCategories;

    /** @var array */
    private $parentMemory = [];

    private $partAttributes = [];

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

    private function generateUrlWithCartAndView(string $uri, string $cart = null): string
    {
        return str_replace([':view', ':cartId'], [self::VIEW_ID, $cart], $uri);
    }

    private function generateUrlWithOrderAndView(string $uri, int $orderId): string
    {
        return str_replace([':view', ':orderId'], [self::VIEW_ID, $orderId], $uri);
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

    public function getAvailableShippingMethods(array $params): array
    {
        try {
            $shipping_details = $params['shipping_details'];
            $items = $params['items'];

            // We are using cart_id as quote_id for guest checkouts, and turning it into an order at the ending of payment process
            $quoteId = $this->createGuestCart();

            $this->addItemToGuestCart($items, $quoteId);

            $response = $this->httpClient->post($this->generateUrlWithCartAndView(self::GUEST_SHIPPING_COST_URL, $quoteId), [
                'json' => $shipping_details
            ]);

            $shippingInfo = json_decode($response->getBody()->getContents(), true);

            $shippingInfo = array_filter($shippingInfo, function ($costItem) {
                return strtolower($costItem['method_code']) !== strtolower('freeshipping');
            });

            return $shippingInfo;
        } catch (ClientException $exception) {
            throw new TextrailException("Method: getAvailableShippingMethods, Details: " . $exception->getMessage());
        }
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
            $shipping_method_data = explode('|', urldecode($shipping_details['shipping_method']));
            $shipping_carrier = $shipping_method_data[0];
            $shipping_method = $shipping_method_data[1];

            // We are using cart_id as quote_id for guest checkouts, and turning it into an order at the ending of payment process
            $quoteId = $this->createGuestCart();

            $cartItems = $this->addItemToGuestCart($items, $quoteId);

            $shippingInfo = $this->addShippingInformationToGuestCart(
                $quoteId,
                [
                    'shipping_carrier_code' => $shipping_carrier,
                    'shipping_method_code' => $shipping_method,
                    'shipping_address' => $shipping_details['address'],
                    'billing_address' => $shipping_details['address'],
                ]
            );

            $tax = $shippingInfo['totals']['tax_amount'];

            return [
                'cost' => $shippingInfo['totals']['shipping_amount'],
                'tax' => $tax,
                'cart_id' => $quoteId,
                'customer_id' => null,
                'items' => $cartItems,
                'method_code' => $shipping_method,
                'carrier_code' => $shipping_carrier
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
     * @param int $currentPage
     * @param int $pageSize
     * @return array<TextrailPartDTO>
     */
    public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array
    {
      $totalParts = $this->getTextrailTotalPartsCount();
      $url = 'rest/' . Config::get('ecommerce.textrail.store') . '/V1/products';

      $Allparts = [];

      for ($i=0; $i < $totalParts; $i+=$pageSize) {

        $queryParts = [
          'searchCriteria[page_size]' => $pageSize,
          'searchCriteria[currentPage]' => $currentPage,
          'searchCriteria[filter_groups][0][filters][0][field]' => 'website_id',
          'searchCriteria[filter_groups][0][filters][0][value]' => Config::get('ecommerce.textrail.store_id'),
          'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq'

        ];

        $parts = json_decode($this->httpClient->get($url, ['headers' => $this->getHeaders(), 'query' => $queryParts])->getBody()->getContents());

        foreach ($parts->items as $item) {

          $description = '';
          $category_id;
          $manufacturer_id;
          $brand_id = '';
          $images = [];

          $customAttributes = [];

          $isCustomAttribute = true;
          foreach ($item->custom_attributes as $custom_attribute) {

            if ($custom_attribute->attribute_code == 'short_description') {
              $description = $custom_attribute->value;
                $isCustomAttribute = false;
            }

            if ($custom_attribute->attribute_code == 'category_ids' && !empty($custom_attribute->value[0])) {
              $category_id = $custom_attribute->value[0];
                $isCustomAttribute = false;
            }

            if ($custom_attribute->attribute_code == 'manufacturer' && ($custom_attribute->value  > 0)) {
              $manufacturer_id = $custom_attribute->value;
                $isCustomAttribute = false;
            }

            if ($custom_attribute->attribute_code == 'brand_name' && ($custom_attribute->value  > 0)) {
              $brand_id = $custom_attribute->value;
                $isCustomAttribute = false;
            }

            if ($isCustomAttribute) {
                $customAttributes[$custom_attribute->attribute_code] = $custom_attribute->value;
            }

              $isCustomAttribute = true;
          }

          foreach ($item->media_gallery_entries as $img) {
            array_push($images, ['file' => $img->file, 'position' => $img->position ?? 0]);
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
            'images' => $images,
            'custom_attributes' => $customAttributes
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
     * @param array $img
     * @return null|array{imageData: array, fileName: string}
     */
    public function getTextrailImage(array $img): ?array
    {
      $img_url = $this->getTextrailImagesBaseUrl() . $img['file'];
      try {
          $checkFile = get_headers($img_url);

          if ($checkFile[0] == "HTTP/1.1 200 OK") {
              $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
              $explodedImage = explode('/', $img['file']);
              $fileName = $explodedImage[count($explodedImage) - 1];

              return ['imageData' => $imageData, 'fileName' => $fileName];
          }
      } catch (\Exception $exception) {
          Log::error('Part image read failed', [
              'image_url' => $img_url,
          ]);

          return null;
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
        'searchCriteria[filter_groups][0][filters][0][value]' => Config::get('ecommerce.textrail.store_id'),
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
     * @param string $poNumber
     * @return int order id
     * @throws \App\Exceptions\Ecommerce\TextrailException when some error occurs on the Magento side
     */
    public function createOrderFromGuestCart(string $cartId, string $poNumber): int
    {
        $url = $this->generateUrlWithCartAndView(self::GUEST_CART_CREATE_ORDER, $cartId);
        $response = $this->httpClient->put($url, [
                'json' => [
                    'paymentMethod' => [
                        'method' => config('ecommerce.textrail.payment_method'),
                        'po_number' => $poNumber
                    ]
                ],
            ]
        );

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return (int)json_decode($response->getBody()->getContents(), true);
        }

        throw new TextrailException('Order creation for session cart has failed.');
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-guest/tag/guest-cartscartIdshipping-information#operation/checkoutGuestShippingInformationManagementV1SaveAddressInformationPost
     *
     * @param string $cartId
     * @param array{shipping_carrier_code: string, shipping_method_code: string, shipping_address: array, billing_address: array} $shippingInformation
     * @return array
     */
    public function addShippingInformationToGuestCart(string $cartId, array $shippingInformation): array
    {
        $url = $this->generateUrlWithCartAndView(self::GUEST_CART_ADD_SHIPPING_INFO , $cartId);

        $response = $this->httpClient->post($url, [
                'json' => ['addressInformation' => $shippingInformation]
            ]
        );

        if ($response->getStatusCode() === BaseResponse::HTTP_OK) {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new TextrailException('Order creation for session cart has failed.');
    }

    public function createOrderFromCart(string $cartId): string
    {
        throw new NotImplementedException;
    }

    /**
     * @see https://magento.redoc.ly/2.3.7-admin/tag/ordersid#operation/salesOrderRepositoryV1GetGet
     * @param int $orderId
     * @return array
     */
    public function getOrderInfo(int $orderId): array
    {
        $endpoint = $this->generateUrlWithOrderAndView(self::ORDER_GET_INFO, $orderId);

        $response = $this->httpClient->get($endpoint, ['headers' => $this->getHeaders()]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @see https://magento.redoc.ly/2.4.3-admin/tag/returns/#operation/rmaRmaManagementV1SaveRmaPost
     *
     * @param RefundBag $refundBag
     * @return array
     */
    public function requestReturn(RefundBag $refundBag): array
    {
        $endpoint = $this->generateUrlWithOrderAndView(self::ORDER_CREATE_RETURN, $refundBag->order->ecommerce_order_id);

        $itemDefaults = [
            'reason' => config('ecommerce.textrail.return.item_default_reason'),
            'condition' => config('ecommerce.textrail.return.item_default_condition'),
            'resolution' => config('ecommerce.textrail.return.item_default_resolution'),
            'status' => config('ecommerce.textrail.return.item_default_status')
        ];

        $items = collect($refundBag->textrailItems)->map(static function (array $item) use ($itemDefaults): array {
            return $item + $itemDefaults;
        })->toArray();

        $response = $this->httpClient->post($endpoint,
            [
                'headers' => $this->getHeaders(),
                'json' => [
                    'rmaDataObject' => [
                        'order_id' => $refundBag->order->ecommerce_order_id,
                        'store_id' => config('ecommerce.textrail.store_id'),
                        'status' => config('ecommerce.textrail.return.default_status'),
                        'items' => $items
                    ]
                ]
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @see https://devdocs.magento.com/guides/v2.4/rest/tutorials/orders/order-issue-refund.html
     * @see https://magento.redoc.ly/2.3.7-admin/tag/orderorderIdrefund#operation/salesRefundOrderV1ExecutePost
     *
     * @return int the memo id
     */
    public function createRefund(int $textrailOrderId, array $items): int
    {
        $endpoint = $this->generateUrlWithOrderAndView(self::ORDER_CREATE_REFUND, $textrailOrderId);

        $requestInfo = [
            'notify' => true,
            'items' => $items,
            'arguments' => [
                'shipping_amount' => 0,
                'adjustment_positive' => 0,
                'adjustment_negative' => 0,
                'extension_attributes' => [
                    'return_to_stock_items' => collect($items)->pluck('order_item_id')->toArray()
                ],
            ]
        ];

        $response = $this->httpClient->post($endpoint, ['headers' => $this->getHeaders(), 'json' => $requestInfo]);

        return (int)json_decode($response->getBody()->getContents(), true);
    }

    public function getTextrailDumpStock(): array
    {
        $url = $this->generateUrlWithCartAndView(self::TEXTRAIL_DUMP_STOCK_URL);
        $stocks = json_decode($this->httpClient->get($url, ['headers' => $this->getHeaders()])->getBody()->getContents());

        $availableStocks = [];
        foreach ($stocks as $stock) {
            if ($stock->qty > 0) {
                $availableStocks[$stock->sku] = $stock->qty;
            }
        }

        return $availableStocks;
    }

    public function getAttributes(): array
    {
        return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_GENERIC_URL . '?searchCriteria[page_size]=1000&fields=items[attribute_code,default_frontend_label,is_visible_on_front,frontend_input,options],total_count&searchCriteria[currentPage]=1', ['headers' => $this->getHeaders()])->getBody()->getContents(), true);
    }

    public function getAttribute(string $code): array
    {
        return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_GENERIC_URL . $code, ['headers' => $this->getHeaders()])->getBody()->getContents(), true);
    }

    public function getTextrailCategories(): array
    {
        $categories = json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_LIST_URL . '?searchCriteria[page_size]=10000', ['headers' => $this->getHeaders()])->getBody()->getContents(), true);

        $this->allCategories = $categories;

        return $categories;
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function getTextrailParentCategory(int $category_id): array
    {
        $category = json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $category_id, ['headers' => $this->getHeaders()])->getBody()->getContents(), true);
        $path = $category['path'];

        $breadcrumb = explode('/', $path);

        $parent = [];
        // 0 = Root, 1 = Shop By, 2 = Available Master Parent ID
        if (!empty($breadcrumb[2])) {
            if (empty($this->parentMemory[$breadcrumb[2]])) {
                $parent = json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $breadcrumb[2], ['headers' => $this->getHeaders()])->getBody()->getContents(), true);
                $this->parentMemory[$breadcrumb[2]] = $parent;
            } else {
                $parent = $this->parentMemory[$breadcrumb[2]];
            }
        }

        return [$parent, $category];
    }
}
