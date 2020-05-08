<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Repositories\Parts\PartRepository;
use App\Models\Parts\Manufacturer;
use App\Models\Parts\Brand;
use App\Models\Parts\Type;
use App\Models\Parts\Category;

/**
 * Class SyncPartsCommand
 */
class SyncPartsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "sync:parts";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Syncs parts to new table";


    private $imagePrefix = 'http://distillery-trailercentral.s3.amazonaws.com';
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parts = new PartRepository();
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->query("SELECT * FROM `parts` WHERE dealer_id = 7439 AND sku NOT IN (SELECT sku FROM parts_v1 WHERE dealer_id = 7439)");
        $getImages = $pdo->prepare("SELECT * FROM parts_image WHERE part_id = :part_id ");
        
        while($row = $stmt->fetch()) {
            
            try {
                
                $getImages->execute([
                    'part_id' => $row['id']
                ]);
                $partImages = $getImages->fetchAll(\PDO::FETCH_ASSOC);
                
                $manufacturer = Manufacturer::where('name', $row['manufacturer'])->first();
                $brand = Brand::where('name', $row['brand'])->first();
                $type = Type::where('name', $row['type'])->first();
                $category = Category::where('name', $row['category'])->first();
                $newImages = [];
                
                foreach($partImages as $image) {
                    $newImages[] = [
                        'url' => $this->imagePrefix . $image['src'],
                        'position' => $image['position']
                    ];
                }

                if(empty($manufacturer) && !empty($row['manufacturer'])) {
                    // Migrate the manufacturer
                    $manufacturer = Manufacturer::create([
                        'name' => $row['manufacturer']
                    ]);

                } 

                if(empty($brand) && !empty($row['brand'])) {
                    // Migrate the brand
                    $brand = Brand::create([
                        'name' => $row['brand']
                    ]);

                } else if (empty($brand) && empty($row['brand'])) {
                    $brand = Brand::first();
                }

                if(empty($type) && !empty($row['type'])) {
                    // Migrate the type
                    $type = Type::create([
                        'name' => $row['type']
                    ]);

                } else if (empty($type) && empty($row['type'])) {
                    $type = Type::where('name', 'Misc')->first();
                }

                if(empty($category) && !empty($row['category'])) {
                    // Migrate the category
                    $category = Category::create([
                        'name' => $row['category']
                    ]);

                } else if (empty($category) && empty($row['category'])) {
                    $category = Category::first();
                }              
                
                $parts->create([
                    'dealer_id' => $row['dealer_id'],
                    'vendor_id' => empty($row['vendor_id']) ? null : $row['vendor_id'],
                    'manufacturer_id' => $manufacturer->id ?? null,
                    'brand_id' => $brand->id,
                    'title' => $row['title'],
                    'type_id' => $type->id,
                    'category_id' => $category->id,
                    'subcategory' => $row['subcategory'],
                    'sku' => $row['sku'],
                    'price' => $row['price'],
                    'dealer_cost' => $row['dealer_cost'],
                    'msrp' => $row['msrp'],
                    'weight' => $row['weight'],
                    'weight_rating' => $row['weight_rating'],
                    'description' => $row['description'],
                    'qty' => $row['qty'],
                    'show_on_website' => $row['show_on_website'],
                    'is_vehicle_specific' => $row['is_vehicle_specific'],
                    'vehicle_make' => $row['vehicle_specific_make'],
                    'vehicle_model' => $row['vehicle_specific_model'],
                    'vehicle_year_from' => $row['vehicle_specific_year_from'],
                    'vehicle_year_to' => $row['vehicle_specific_year_to'],
                    'qb_id' => $row['qb_id'],
                    'images' => $newImages,
                    'video_embed_code' => $row['video_embed_code']
                ]);
                
            } catch (\Exception $ex) {
                
                $this->info($ex->getMessage());
                
            }
            
            
        }
    }
}
