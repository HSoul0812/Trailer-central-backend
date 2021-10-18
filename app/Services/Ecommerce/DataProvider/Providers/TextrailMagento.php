<?php
namespace App\Services\Ecommerce\DataProvider\Providers;

use App\Services\Ecommerce\DataProvider\DataProviderInterface;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client as GuzzleHttpClient;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;

class TextrailMagento implements DataProviderInterface, TextrailMagentoInterface
{

    private const TEXTRAIL_CATEGORY_URL = 'rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'media/catalog/product';

    /** @var string */
    private $apiUrl;

    /** @var GuzzleHttpClient */
    private $httpClient;

    /** @var string */
    private $token;

    /**
     * TextrailMagento constructor.
     * @param string $apiUrl
     */
    public function __construct()
    {
        $this->apiUrl = 'https://mcstaging.textrail.com/';
        $this->httpClient = new GuzzleHttpClient(['base_uri' => $this->apiUrl]);
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

    public function estimateShippingCost(array $params)
    {
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
          
        }
        
        array_push($Allparts, $dtoTextrail);
        
        
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

    public function getTextrailImage(array $img): ?array
    {
      $img_url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_ATTRIBUTES_MEDIA_URL . $img['file'];
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