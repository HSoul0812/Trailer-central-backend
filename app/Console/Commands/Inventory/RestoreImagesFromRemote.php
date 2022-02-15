<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryImage;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Image;
use App\Models\User\User;
use PDO;
/**
 * Class RemoveBrokenFirstImage
 * @package App\Console\Commands\Inventory
 */
class RestoreImagesFromRemote extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:restore-images-from-remote {remote_db_host} {db_name} {user} {password} {dealer_id?}";

    /**
     * @var InventoryServiceInterface
     */
    private $inventoryService;
    
    /** 
     * @var PDO
     */
    private $remoteDb;
    
    /**    
     * @var int|null
     */
    private $dealerId;

    /**
     * DeleteDuplicates constructor.
     * @param InventoryServiceInterface $inventoryService
     */
    public function __construct(InventoryServiceInterface $inventoryService)
    {
        parent::__construct();

        $this->inventoryService = $inventoryService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        
        $this->dealerId = $this->argument('dealer_id');
                
        $this->getRemoteInventory(function($inventory) {
            $this->restoreRemoteInventoryImages($inventory['inventory_id']);
        });
        

        return true;
    }
    
    private function getRemoteDbConnection() : PDO
    {
        if ($this->remoteDb) {
            return $this->remoteDb;
        }
        
        $host       = $this->argument('remote_db_host');
        $dbName     = $this->argument('db_name');
        $user       = $this->argument('user');
        $password   = $this->argument('password');
        
        return $this->remoteDb = new PDO("mysql:host=$host;dbname=$dbName", $user, $password);
    }
    
    private function getRemoteInventory($func) : void
    {
        $connection = $this->getRemoteDbConnection();
        
        if ($this->dealerId) {
            $users = User::where('dealer_id', $this->dealerId)->get();
        } else {
            $users = User::where('dealer_id', '>', 1000)->get();
        }
                
        foreach($users as $user) {
            if ($user->dealer_id) {
                $select = $connection->prepare("SELECT * FROM inventory WHERE is_archived = 0 AND status != 2 AND dealer_id = '{$user->dealer_id}'");
            } else {
                $select = $connection->prepare("SELECT * FROM inventory WHERE is_archived = 0 AND status != 2");
            }

            $select->execute();

            while($inventoryRow = $select->fetch(\PDO::FETCH_ASSOC)) {
                $func($inventoryRow);
            }
        }
        
    }
    
    private function restoreRemoteInventoryImages(int $inventoryId) : bool
    {
        $imagesQuery = $this->getRemoteDbConnection()->prepare("select * from image join inventory_image on inventory_image.image_id = image.image_id where inventory_image.inventory_id = {$inventoryId}");
        $imagesQuery->execute();
        
        $inventory = Inventory::where('inventory_id', $inventoryId)->first();
        
        if (empty($inventory)) {            
            return false;
        }

        
       
        DB::transaction(function() use ($inventory, $imagesQuery) {
            
            $images = InventoryImage::where('inventory_id', $inventory->inventory_id)->get();
//            InventoryImage::where('inventory_id', $inventory->inventory_id)->delete();
            
            if ($images->count() === 0) {                
                while($image = $imagesQuery->fetch(\PDO::FETCH_ASSOC)) {
                    try {
                        $newImage = Image::create($image);
                        $image['image_id'] = $newImage->image_id;
                        InventoryImage::create($image); 
                    } catch (\Exception $ex) {
                        $this->error($ex->getMessage());
                    }
                    
                    $this->info("Restoring inventory images for {$inventory->stock}");
                }
            }   
        });
                
        return true;

    }
}
