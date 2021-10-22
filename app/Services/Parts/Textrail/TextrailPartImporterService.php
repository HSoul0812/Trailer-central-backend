<?php

namespace App\Services\Parts\Textrail;


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

class TextrailPartImporterService implements TextrailPartImporterServiceInterface
{
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

   public function run()
   {
     
     $parts = $this->textrailPartService->getAllParts();
     
     foreach ($parts as $item) {

       $textrailCategory = $this->textrailPartService->getTextrailCategory($item->category_id);

       $categoryParams = [
         'name' => $textrailCategory->name
       ];

       $category = $this->categoryRepository->firstOrCreate($categoryParams);

       $item->category_id = $category->id;
       
       $textrailCategoryForType = $this->textrailPartService->getTextrailCategory($textrailCategory->parent_id);
       
       $typeParams = [
         'name' => $textrailCategoryForType->name
       ];

       $type = $this->typeRepository->firstOrCreate($typeParams);
       $item->type_id = $type->id;
       
       $textrailManufacturers = $this->textrailPartService->getTextrailManufacturers();

       foreach ($textrailManufacturers as $textrailManufacturer) {
         if ($textrailManufacturer->value == $item->manufacturer_id) {

           $manufacturerParams = [
             'name' => $textrailManufacturer->label
           ];

           $manufacturer = $this->manufacturerRepository->firstOrCreate($manufacturerParams);
           $item->manufacturer_id = $manufacturer->id;
         }
         
       }
       
       $textrailBrands = $this->textrailPartService->getTextrailBrands();

       foreach ($textrailBrands as $textrailBrand) {
         if ($textrailBrand->value == $item->brand_id) {

           $brandParams = [
             'name' => $textrailBrand->label
           ];
           
           $brand = $this->brandRepository->firstOrCreate($brandParams);
           $item->brand_id = $brand->id;
         }
         
       }

       $partsParams = $this->textrailPartsTransformer->transform($item);
       $newTextrailPart = $this->partRepo->createOrUpdateBySku($partsParams);

       foreach ($item->images as $img) {

         $textrailImage = $this->textrailPartService->getTextrailImage($img);
         
         if ($textrailImage) {

           $imageParams = [
             'part_id' => $newTextrailPart->id,
             'position' => $img['position']
           ];
           $this->imageRepository->firstOrCreate($imageParams, $textrailImage['fileName'], $textrailImage['imageData']);
         }
       }
     }
   }

}