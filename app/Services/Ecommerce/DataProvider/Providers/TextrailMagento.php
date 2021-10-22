<?php
namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;
use GuzzleHttp\Exception\ClientException;

class TextrailMagento implements DataProviderInterface, TextrailGuestCheckoutInterface
{

    private const TEXTRAIL_CATEGORY_URL = 'rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'media/catalog/product';
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
        $this->httpClient = new GuzzleHttpClient(['base_uri' => $this->apiUrl]);
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

    /**
     * @return array<TextrailPartDTO>
     */

    public function getAllParts(int $currentPage = 1, int $pageSize = 1000): array
    {
      $headers = self::getHeaders();
      
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

        $parts = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $queryParts])->getBody()->getContents());

        foreach ($parts->items as $item) {

          $description;
          $category_id;
          $manufacturer_id;
          $brand_id;
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
      $headers = self::getHeaders();
      return json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $categoryId, ['headers' => $headers])->getBody()->getContents());
    }

    public function getTextrailManufacturers(): array
    {
      $headers = self::getHeaders();
      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL, ['headers' => $headers])->getBody()->getContents());
    }
      
    public function getTextrailBrands(): array
    {
      $headers = self::getHeaders();
      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL, ['headers' => $headers])->getBody()->getContents());
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
      } else {
        return null;
      }
    }

    public function getTextrailImagesBaseUrl()
    {
      return $this->apiUrl . self::TEXTRAIL_ATTRIBUTES_MEDIA_URL;
    }

    public function getTextrailTotalPartsCount(int $pageSize = 1, int $currentPage = 1): int
    {
      $headers = self::getHeaders();

      $query = [
        'searchCriteria[page_size]' => $pageSize,
        'searchCriteria[currentPage]' => $currentPage,
        'searchCriteria[filter_groups][0][filters][0][field]' => 'website_id',
        'searchCriteria[filter_groups][0][filters][0][value]' => 10,
        'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq'
      ];

      $url = 'rest/' . Config::get('ecommerce.textrail')['store'] . '/V1/products';

      $getPart = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $query])->getBody()->getContents());
      $totalParts = $getPart->total_count;
      
      return $totalParts;
    }

    /**
     * @return array{"Content-Type": string, "Authorization": string}
     */

    private function getHeaders(): array
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];
      
      return $headers;
    }

}