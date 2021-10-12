<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Models\Parts\Textrail\Category;
use App\Models\Parts\Textrail\Type;
use App\Models\Parts\Textrail\Manufacturer;
use App\Models\Parts\Textrail\Brand;
use App\Models\Parts\Textrail\Image;
use Illuminate\Support\Facades\Storage;

class GetTextrailParts extends Command
{
    private const TEXTRAIL_PRODUCT_URL = 'https://mcstaging.textrail.com/rest/V1/products?searchCriteria[filter_groups][0][filters][0][field]=status&searchCriteria[filter_groups][0][filters][0][value]=1';
    private const TEXTRAIL_CATEGORY_URL = 'https://mcstaging.textrail.com/rest/V1/categories/';
    private const TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL = 'https://mcstaging.textrail.com/rest/V1/products/attributes/manufacturer/options/';
    private const TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL = 'https://mcstaging.textrail.com/rest/V1/products/attributes/brand_name/options/';
    private const TEXTRAIL_ATTRIBUTES_MEDIA_URL = 'https://mcstaging.textrail.com/media/catalog/product';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:get-textrail-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */

     /**     
      * @var PartRepositoryInterface 
      */
     protected $partRepo;
     
     /**     
      * @var GuzzleHttp\Client
      */
     protected $httpClient;

    public function __construct(PartRepository $partRepository)
    {
        parent::__construct();
        $this->partRepo = $partRepository;
        $this->httpClient = new GuzzleHttpClient();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 0m5elzlp6wp7pofevd0jt2i5w6d038mk',
        ];

        //the first query is to get the total parts from textrail

        $query = [
          'searchCriteria[page_size]' => 1,
          'searchCriteria[currentPage]' => 1
        ];

        $url = self::TEXTRAIL_PRODUCT_URL;

        $getPart = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $query])->getBody()->getContents());
        $totalParts = $getPart->total_count;
        
        $currentPage = 1;
        $pageSize = 1000;
        
        for ($i=0; $i < $totalParts; $i+=$pageSize) {
          
          $queryParts = [
            'searchCriteria[page_size]' => $pageSize,
            'searchCriteria[currentPage]' => $currentPage
          ];
          
          $parts = json_decode($this->httpClient->get($url, ['headers' => $headers, 'query' => $queryParts])->getBody()->getContents());
          
          foreach ($parts->items as $item) {
            $partsParams = [
              'title' => $item->name,
              'sku' => $item->sku,
              'price' => $item->price,
              'weight' => $item->weight ? $item->weight : 0
            ];

            foreach ($item->custom_attributes as $custom_attribute) {
              if ($custom_attribute->attribute_code == 'short_description') {
                $partsParams['description'] = $custom_attribute->value;
              }

              if ($custom_attribute->attribute_code == 'category_ids') {
                
                $textrailCategory = json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $custom_attribute->value[0], ['headers' => $headers])->getBody()->getContents());
                
                $categoryParams = [
                  'name' => $textrailCategory->name
                ];
    
                $category = Category::firstOrCreate($categoryParams);
                $partsParams['category_id'] = $category->id;
                
                if ($textrailCategory->parent_id && ($textrailCategory->parent_id  > 0)) {
                  $textrailCategoryForType = json_decode($this->httpClient->get(self::TEXTRAIL_CATEGORY_URL . $textrailCategory->parent_id, ['headers' => $headers])->getBody()->getContents());
                  
                  $typeParams = [
                    'name' => $textrailCategoryForType->name
                  ];
      
                  $type = Type::firstOrCreate($typeParams);
                  $partsParams['type_id'] = $type->id;
                }
                
                
              }

              if ($custom_attribute->attribute_code == 'manufacturer' && ($custom_attribute->value  > 0)) {
                $textrailManufacturers = json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_MANUFACTURER_URL, ['headers' => $headers])->getBody()->getContents());
                
                foreach ($textrailManufacturers as $textrailManufacturer) {
                  if ($textrailManufacturer->value == $custom_attribute->value) {

                    $manufacturerParams = [
                      'name' => $textrailManufacturer->label
                    ];

                    $manufacturer = Manufacturer::firstOrCreate($manufacturerParams);
                    $partsParams['manufacturer_id'] = $manufacturer->id;
                  }
                  
                }

              }

              if ($custom_attribute->attribute_code == 'brand_name' && ($custom_attribute->value  > 0)) {
                $textrailBrands = json_decode($this->httpClient->get(self::TEXTRAIL_ATTRIBUTES_BRAND_NAME_URL, ['headers' => $headers])->getBody()->getContents());
                
                foreach ($textrailBrands as $textrailBrand) {
                  if ($textrailBrand->value == $custom_attribute->value) {

                    $brandParams = [
                      'name' => $textrailBrand->label
                    ];
                    
                    $brand = Brand::firstOrCreate($brandParams);
                    $partsParams['brand_id'] = $brand->id;
                  }
                  
                }

              }

            }
            
            $newTextrailPart = $this->partRepo->createOrUpdateBySku($partsParams);
            
            foreach ($item->media_gallery_entries as $img) {
              
              $img_url = self::TEXTRAIL_ATTRIBUTES_MEDIA_URL . $img->file;
              $checkFile = get_headers($img_url);
          
              if ($checkFile[0] == "HTTP/1.1 200 OK") {
                $imageData = file_get_contents($img_url, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
                $explodedImage = explode('/', $img->file);
                $fileName = $explodedImage[count($explodedImage) - 1];
                
                Storage::disk('s3')->put($fileName, $imageData, 'public');
                $s3ImageUrl = Storage::disk('s3')->url($fileName);

                $imageParams = [
                  'part_id' => $newTextrailPart->id,
                  'image_url' => $s3ImageUrl,
                  'position' => $img->position
                ];
                Image::firstOrCreate($imageParams);
              }
            }
          }
          
          $currentPage++;
        }
  
    }
}
