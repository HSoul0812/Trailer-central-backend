<?php

namespace App\Services\Parts\Textrail;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Config;


class TextrailPartService implements TextrailPartServiceInterface
{

    private const TEXTRAIL_PRODUCT_URL = 'https://mcstaging.textrail.com/rest/V1/products?searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1';
    private const TEXTRAIL_CATEGORY_URL = 'https://mcstaging.textrail.com/rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'https://mcstaging.textrail.com/rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'https://mcstaging.textrail.com/rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'https://mcstaging.textrail.com/media/catalog/product';

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpClient();
    }

    public function getAllParts()
    {
      
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];
      
      $totalParts = $this->getTextrailTotalPartsCount();
      $url = self::TEXTRAIL_PRODUCT_URL;
      
      $currentPage = 1;
      $pageSize = 1;
      $Allparts = [];

      for ($i=0; $i < 2; $i+=$pageSize) {
        $queryParts = [
          'searchCriteria[page_size]' => $pageSize,
          'searchCriteria[currentPage]' => $currentPage
        ];
        
        $parts = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $queryParts])->getBody()->getContents());
        $Allparts = array_merge($Allparts, $parts->items);
        
        $currentPage++;
      }
      
      return $Allparts;
    }

    public function getTextrailTotalPartsCount()
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];

      $query = [
        'searchCriteria[page_size]' => 1,
        'searchCriteria[currentPage]' => 1
      ];
      
      $url = self::TEXTRAIL_PRODUCT_URL;

      $getPart = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $query])->getBody()->getContents());
      $totalParts = $getPart->total_count;
      
      return $totalParts;
    }
    
    public function getTextrailCategory($categoryId)
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];

      return json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $categoryId, ['headers' => $headers])->getBody()->getContents());
    }

    
    public function getTextrailManufacturers()
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];

      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL, ['headers' => $headers])->getBody()->getContents());
    }
      
    public function getTextrailBrands()
    {
      $bearer = Config::get('ecommerce.textrail')['bearer'];
      $headers = [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $bearer,
      ];

      return json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL, ['headers' => $headers])->getBody()->getContents());
    }

    public function getTextrailImage($img)
    {
      $img_url = self::TEXTRAIL_ATTRIBUTES_MEDIA_URL . $img->file;
      $checkFile = get_headers($img_url);

      if ($checkFile[0] == "HTTP/1.1 200 OK") {
        $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        $explodedImage = explode('/', $img->file);
        $fileName = $explodedImage[count($explodedImage) - 1];
        
        return ['imageData' => $imageData, 'fileName' => $fileName];
      } else {
        return false;
      }
    }

}