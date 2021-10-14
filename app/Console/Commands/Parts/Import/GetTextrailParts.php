<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Repositories\Parts\Textrail\BrandRepositoryInterface;
use App\Repositories\Parts\Textrail\TypeRepositoryInterface;
use App\Repositories\Parts\Textrail\ManufacturerRepositoryInterface;
use App\Repositories\Parts\Textrail\CategoryRepositoryInterface;
use App\Repositories\Parts\Textrail\ImageRepositoryInterface;
use App\Services\Parts\Textrail\TextrailPartServiceInterface;
use App\Transformers\Parts\Textrail\TextrailPartsTransformer;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use App\Models\Parts\Textrail\Category;
use App\Models\Parts\Textrail\Type;
use App\Models\Parts\Textrail\Manufacturer;
use App\Models\Parts\Textrail\Brand;
use App\Models\Parts\Textrail\Image;
use Illuminate\Support\Facades\Storage;

class GetTextrailParts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     /**
      * @var \App\Services\Parts\Textrail\TextrailPartServiceInterface;
      */
     protected $textrailPartService;

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

    public function __construct(
      PartRepository $partRepository, 
      TextrailPartServiceInterface $textrailPartService,
      CategoryRepositoryInterface $categoryRepository,
      BrandRepositoryInterface $brandRepository,
      ManufacturerRepositoryInterface $manufacturerRepository,
      TypeRepositoryInterface $typeRepository,
      ImageRepositoryInterface $imageRepository,
      TextrailPartsTransformer $textrailPartsTransformer,
      Manager $manager
      )
    {
        parent::__construct();
        $this->partRepo = $partRepository;
        $this->httpClient = new GuzzleHttpClient();
        $this->textrailPartService = $textrailPartService;
        $this->categoryRepository = $categoryRepository;
        $this->brandRepository = $brandRepository;
        $this->manufacturerRepository = $manufacturerRepository;
        $this->typeRepository = $typeRepository;
        $this->imageRepository = $imageRepository;
        $this->textrailPartsTransformer = $textrailPartsTransformer;
        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
     public function handle()
     {
       
       $parts = $this->textrailPartService->getAllParts();
       
       foreach ($parts as $item) {
         
         foreach ($item->custom_attributes as $custom_attribute) {
           if ($custom_attribute->attribute_code == 'short_description') {
             $item->description = $custom_attribute->value;
           }

           if ($custom_attribute->attribute_code == 'category_ids') {
             $textrailCategory = $this->textrailPartService->getTextrailCategory($custom_attribute->value[0]);

             $categoryParams = [
               'name' => $textrailCategory->name
             ];
 
             $category = $this->categoryRepository->firstOrCreate($categoryParams);
  
             $item->category_id = $category->id;
             
             if ($textrailCategory->parent_id && ($textrailCategory->parent_id  > 0)) {
               $textrailCategoryForType = $this->textrailPartService->getTextrailCategory($textrailCategory->parent_id);
               
               $typeParams = [
                 'name' => $textrailCategoryForType->name
               ];
   
               $type = $this->typeRepository->firstOrCreate($typeParams);
               $item->type_id = $type->id;
             }
           }
           
           if ($custom_attribute->attribute_code == 'manufacturer' && ($custom_attribute->value  > 0)) {
             $textrailManufacturers = $this->textrailPartService->getTextrailManufacturers();

             foreach ($textrailManufacturers as $textrailManufacturer) {
               if ($textrailManufacturer->value == $custom_attribute->value) {

                 $manufacturerParams = [
                   'name' => $textrailManufacturer->label
                 ];

                 $manufacturer = $this->manufacturerRepository->firstOrCreate($manufacturerParams);
                 $item->manufacturer_id = $manufacturer->id;
               }
               
             }
           }
           
           if ($custom_attribute->attribute_code == 'brand_name' && ($custom_attribute->value  > 0)) {
             $textrailBrands = $this->textrailPartService->getTextrailBrands();

             foreach ($textrailBrands as $textrailBrand) {
               if ($textrailBrand->value == $custom_attribute->value) {

                 $brandParams = [
                   'name' => $textrailBrand->label
                 ];
                 
                 $brand = $this->brandRepository->firstOrCreate($brandParams);
                 $item->brand_id = $brand->id;
               }
               
             }
           }
         }

         $partsParams = $this->textrailPartsTransformer->transform($item);
         $newTextrailPart = $this->partRepo->createOrUpdateBySku($partsParams);

         foreach ($item->media_gallery_entries as $img) {
           $textrailImage = $this->textrailPartService->getTextrailImage($img);
           
           if ($textrailImage) {
             Storage::disk('s3')->put($textrailImage['fileName'], $textrailImage['imageData'], 'public');
             $s3ImageUrl = Storage::disk('s3')->url($textrailImage['fileName']);

             $imageParams = [
               'part_id' => $newTextrailPart->id,
               'image_url' => $s3ImageUrl,
               'position' => $img->position
             ];
             $this->imageRepository->firstOrCreate($imageParams);
           }
         }
       }
     }

}
