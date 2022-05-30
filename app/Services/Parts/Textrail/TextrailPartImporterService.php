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
use App\Models\Parts\Textrail\Part;
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
        $stocks = $this->textrailPartService->getTextrailDumpStock();

        $parts = $this->textrailPartService->getAllParts();
        $parts_sku = [];

        foreach ($parts as $item) {
            $parts_sku[] = $item->sku;

            // Prevent import 0 qty items to DB.
            if (!array_key_exists($item->sku, $stocks)) {
                continue;
            }

            $trashed_item = $this->partRepo->getBySkuWithTrashed($item->sku);
            if ($trashed_item) {
                $trashed_item->restore();
            }

            list($parentCategory, $textrailCategory) = $this->textrailPartService->getParentAndCategory($item->category_id);

            $categoryParams = [
                'name' => $textrailCategory['name'],
            ];

            $category = $this->categoryRepository->firstOrCreate($categoryParams);

            $item->category_id = $category->id;

            $typeParams = [
                'name' => $parentCategory['name']
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

            $item->qty = $stocks[$item->sku] ?? 0;

            $partsParams = $this->textrailPartsTransformer->transform($item);
            $newTextrailPart = $this->partRepo->createOrUpdateBySku($partsParams);

            $newTextrailPart->images()->delete();

            if (count($item->images) > 0) {
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
            } else {
                $textrailImage = $this->textrailPartService->getTextrailPlaceholderImage();

                if ($textrailImage) {

                    $imageParams = [
                        'part_id' => $newTextrailPart->id,
                        'position' => $img['position']
                    ];
                    $this->imageRepository->firstOrCreate($imageParams, $textrailImage['fileName'], $textrailImage['imageData']);
                }
            }

        }

        $textrailParts = $this->partRepo->getAllExceptBySku($parts_sku);

        foreach ($textrailParts as $textrailPart) {
            Part::withoutSyncingToSearch(function () use ($textrailPart) {
                $textrailPart->delete();
            });
        }
    }
}
