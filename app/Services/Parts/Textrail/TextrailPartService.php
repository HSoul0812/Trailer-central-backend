<?php

namespace App\Services\Parts\Textrail;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Config;
use App\Services\Parts\Textrail\DTO\TextrailPartDTO;


class TextrailPartService implements TextrailPartServiceInterface
{

    private const TEXTRAIL_PRODUCT_URL = "rest/V1/products?searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1";
    private const TEXTRAIL_CATEGORY_URL = 'rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'media/catalog/product';

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
    }

    public function getAllParts($currentPage = 1, $pageSize = 1000): array
    {
      $headers = self::getHeaders();
      
      $totalParts = $this->getTextrailTotalPartsCount();
      $url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_PRODUCT_URL;

      $Allparts = [];

      for ($i=0; $i < $totalParts; $i+=$pageSize) {
        $queryParts = [
          'searchCriteria[page_size]' => $pageSize,
          'searchCriteria[currentPage]' => $currentPage
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
            'weight' =>$item->weight ? $item->weight : '',
            'description' => $description,
            'category_id' => $category_id,
            'manufacturer_id' => $manufacturer_id,
            'brand_id' => $brand_id,
            'images' => $images
          ]);
          
        }
        
        array_push($Allparts, $dtoTextrail);
        
        
        $currentPage++;
      }

      return $Allparts;
    }

    public function getTextrailTotalPartsCount($pageSize = 1, $currentPage = 1): int
    {
      $headers = self::getHeaders();

      $query = [
        'searchCriteria[page_size]' => $pageSize,
        'searchCriteria[currentPage]' => $currentPage
      ];
      
      $url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_PRODUCT_URL;

      $getPart = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $query])->getBody()->getContents());
      $totalParts = $getPart->total_count;
      
      return $totalParts;
    }
    
    public function getTextrailCategory(int $categoryId): object
    {
      $headers = self::getHeaders();
      $url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_CATEGORY_URL;
      return json_decode($this->httpClient->get($url . $categoryId, ['headers' => $headers])->getBody()->getContents());
    }

    
    public function getTextrailManufacturers(): array
    {
      
      $headers = self::getHeaders();
      $url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL;
      return json_decode($this->httpClient->get($url, ['headers' => $headers])->getBody()->getContents());
    }
      
    public function getTextrailBrands(): array
    {
      $headers = self::getHeaders();
      $url = env('TEXTRAIL_API_URL') . self::TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL;
      return json_decode($this->httpClient->get($url, ['headers' => $headers])->getBody()->getContents());
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