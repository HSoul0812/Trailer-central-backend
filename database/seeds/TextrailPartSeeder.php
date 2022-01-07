<?php

use Illuminate\Database\Seeder;
use App\Models\Parts\Textrail\Type;
use App\Models\Parts\Textrail\Brand;
use App\Models\Parts\Textrail\Manufacturer;
use App\Models\Parts\Textrail\Category;
use App\Models\Parts\Textrail\Part;
use App\Models\Parts\Textrail\Image;
use Illuminate\Database\Eloquent\Collection;

class TextrailPartSeeder extends Seeder
{
    private const AVAILABLE_IMAGE_LIST = [
        'https://static-trailercentral.s3.amazonaws.com/files/Chilaquiles-verdes-fa%CC%81ciles.jpeg',
        'https://static-trailercentral.s3.amazonaws.com/files/colombias-bandeja-paisa.jpeg',
        'https://static-trailercentral.s3.amazonaws.com/files/http___cdn.cnn.com_cnnnext_dam_assets_200401171739-06-best-turkish-foods-yaprak-dolma.jpg',
        'https://static-trailercentral.s3.amazonaws.com/files/pakistan-trip-42-X3.jpeg',
        'https://static-trailercentral.s3.amazonaws.com/files/sashimi-resized.jpeg'
    ];
    
    private const AMOUNT_OF_PARTS_TO_CREATE = 4500;
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {               
        $this->cleanTables();
        $categories = $this->createCategories();
        $brands = $this->createBrands();
        $types = $this->createTypes();
        $manufacturers = $this->createManufacturers();
        
        $parts = factory(Part::class, self::AMOUNT_OF_PARTS_TO_CREATE)->create([
            "manufacturer_id" => $manufacturers->random()->id,
            "brand_id" => $brands->random()->id,
            "type_id" => $types->random()->id,
            "category_id" => $categories->random()->id,
        ]);         
        
        $partPosition = 0;
        foreach($parts as $part) {
            
            if ($partPosition % 2) {
              foreach(self::AVAILABLE_IMAGE_LIST as $imageUrl) {
                Image::create([
                   'part_id' => $part->id,
                   'position' => $part->images->count(),
                   'image_url' => $imageUrl
                ]);  
              }
            }            
            
            $partPosition++;
        }
        
    }
    
    private function cleanTables(): void
    {
        Type::truncate();
        Brand::truncate();
        Manufacturer::truncate();
        Category::truncate();
        Part::truncate();
    }
    
    private function createCategories(int $categoriesToCreate = 20): Collection
    {
        return factory(Category::class, $categoriesToCreate)->create();
    }
    
    private function createTypes(int $typesToCreate = 20): Collection
    {
        return factory(Type::class, $typesToCreate)->create();
    }
    
    private function createManufacturers(int $makesToCreate = 100): Collection
    {
        return factory(Manufacturer::class, $makesToCreate)->create();
    }
    
    private function createBrands(int $brandsToCreate = 100): Collection
    {
        return factory(Brand::class, $brandsToCreate)->create();
    }
}
