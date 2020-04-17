<?php

namespace App\Models\Inventory;

use App\Models\Upload\Image;
use App\Traits\CompactHelper;
use App\Traits\GeospatialHelper;
use App\Traits\ImageHelper;
use App\Traits\UploadConst;
use App\Traits\UploadHelper;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Inventory extends Model
{
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'inventory_id',
        'entity_type_id',
        'dealer_id',
        'dealer_location_id',
        'created_at',
        'updated_at',
        'active',
        'title',
        'stock',
        'manufacturer',
        'brand',
        'model',
        'description',
        'video_embed_code',
        'category',
        'vin',
        'geolocation',
        'msrp',
        'price',
        'use_website_price',
        'website_price',
        'dealer_price',
        'monthly_payment',
        'year',
        'condition',
        'length',
        'width',
        'height',
        'weight',
        'gvwr',
        'axle_capacity',
        'cost_of_unit',
        'cost_of_shipping',
        'cost_of_prep',
        'total_of_cost',
        'minimum_selling_price',
        'notes',
        'is_sold',
        'is_special',
        'is_featured',
        'show_on_ksl',
        'show_on_racingjunk',
        'show_on_website',
        'overlay_enabled',
        'status',
        'is_consignment',
        'is_archived',
        'sales_price',
        'height_display_mode',
        'width_display_mode',
        'length_display_mode',
        'height_inches',
        'width_inches',
        'length_inches',
        'show_on_rvtrader',
        'payload_capacity',
        'chosen_overlay'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Updates the inventory_update table when needed.
     *
     * @param $type string 'insert', 'update', 'delete', 'unarchive', 'archive', etc
     */
    public function updateTable($type, $force_location_id = false)
    {
        //TODO for what?
        if ($type != 'delete' && $type != 'delete-update') {
            $action = 'update';
        } else {
            $action = 'delete';
        }

        if ($force_location_id !== false) {
            $location_id = $force_location_id;
        } else {
            $location_id = $this->getData('dealer_location_id');
        }

        $sql = "REPLACE INTO `inventory_update` SET `inventory_id` = :inventoryId, `dealer_id` = :dealerId, stock = :stock, location_id = :location_id, action = :action, specific_action = :specificAction, time_entered = :time, processed = 0";
        $stmt = Db_Manager::getConnection()->prepare($sql);
        $stmt->execute(array(
            'inventoryId' => $this->getData('inventory_id'),
            'dealerId' => $this->getData('dealer_id'),
            'stock' => $this->getData('stock'),
            'location_id' => $location_id,
            'action' => $action,
            'specificAction' => $type,
            'time' => time(),
        ));
    }

    // Upload Image
    public function _uploadImage($url)
    {
        // Get File Path For Image
        $filepath = UploadHelper::getUploadDirectory(UploadConst::UPLOAD_TYPE_IMAGE, array(
            $this->dealer_id,
            $this->id
        ));

        // Create Directory, If It Doesn't Already Exist
        UploadHelper::createDirectory($filepath, 0775);
        $tempname = UploadHelper::hash(time()) . base_convert(rand(1, getrandmax()), 10, 36);
        $filename = $filepath . DS . $tempname . ".tmp";
        $extension = "";

        // Rename URL to Filename
        try {
            // Save the File
            rename($url, $filename);
        } catch (Exception $e) {
            Log::error("Could not save file to '{$filename}'. Reason: " . $e->getMessage());
            return 'save-failed';
        }

        // Image No Longer Existed?
        if (!file_exists($filename)) {
            Log::error("Uploaded file '{$filename}' not found.");
            return 'upload-failed';
        }

        // No Data
        $imageinfo = getimagesize($filename);
        if ($imageinfo === false) {
            Log::error("Uploaded file '{$filename}' is empty.");
            return 'upload-failed';
        }

        // File Exists?
        if (file_exists($filename)) {
            Log::debug("Uploaded file '{$filename}' was found.");

            // Get Extension
            $imageinfo = getimagesize($filename);
            $mimetype = $imageinfo['mime'];

            // No Extension
            $extension = "";
            switch ($mimetype) {
                case "image/gif":
                    $extension = "gif";
                    break;
                case "image/jpeg":
                    $extension = "jpg";
                    break;
                case "image/png":
                    $extension = "png";
                    break;
                default:
                    $extension = "";
                    break;
            }

            // Valid Extension? Finish Saving File
            if ($extension != "") {
                // Get Filename
                $inventoryFilenameTitle = $this->title . "_clapp1" . ".{$extension}";

                $newfilename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array(
                    '_',
                    '.',
                    ''
                ), $inventoryFilenameTitle);

                // Resize Image
                ImageHelper::resize($filename, 800, 800, true, $filename);

                // Upload File to S3
                $path = UploadHelper::getS3Path($newfilename, array(
                    $this->dealer_id,
                    $this->id
                ));

                UploadHelper::putImageToS3($filename, $path, $mimetype);

                // Delete Old File
                unlink($filename);

                // New Filename Exists?
                Log::info("File '{$filename}' renamed to final '{$path}'.");
                $filename = '/' . $path;
            } else {
                Log::error("Uploaded data is not of expected mime-type. Required: image/png, image/jpeg, image/gif. Given: {$mimetype}.");
                return 'image-invalid';
            }
        }

        // Return Filename
        return $filename;
    }

    public function getIdentifier()
    {
        $inventoryIdentifier = CompactHelper::shorten($this->inventory_id);

        return $inventoryIdentifier;
    }

    public function getDealerIdentifier()
    {
        return CompactHelper::shorten($this->dealer_id);
    }

    public function getDealerLocationIdentifier()
    {
        return CompactHelper::shorten($this->dealer_location_id);
    }

    static function inventoryByStockAndDealer($stock, $dealerId)
    {
        if (!!$stock && !!$dealerId) {
            return DB::table(self::getTable())->where([['stock', $stock], ['dealer_id', $dealerId]])->get();
        } else {
            return false;
        }
    }

    static function inventoryByDealer($dealerId)
    {
        return response("You should use Dealer model instead.", '500');
    }

    static function inventoryByLocation($locationId, $offset = 0, $limit = false, $active = true)
    {
        return response("You should use Location model instead.", '500');
    }

    static function inventoryCountForStockAndDealer($stock, $dealerId)
    {
        if (!!$stock && !!$dealerId) {
            return DB::table(self::getTable())->where([['stock', $stock], ['dealer_id', $dealerId]])->count()->get();
        }
    }

    public function getLatitude()
    {
        if (!!$this->geolocation) {
            $data = GeospatialHelper::FromWKB($this->geolocation);

            return $data['lat'];
        } else {
            return null;
        }
    }

    public function setLatitude($latitude)
    {
        $longitude = $this->getLongitude();
        if (empty($longitude) || is_null($longitude)) {
            $longitude = 0;
        }
        $this->geolocation = GeospatialHelper::ToWKB($latitude, $longitude);
    }

    public function getLongitude()
    {
        $geolocation = $this->getData('geolocation');
        if ($geolocation) {
            $data = GeospatialHelper::FromWKB($geolocation);

            return $data['lon'];
        } else {
            return null;
        }
    }

    public function getImages()
    {
        //TODO renew function for laravel
        $query = new Db_Query('inventory_image');

        $query->add('inventory_id', $this->id);
        $query->order('IFNULL(position, 99)', 'ASC');
        $query->order('inventory_image.image_id', 'ASC');

        $inventoryImageData = $query->doSelect();

        $imageData = array();
        while ($row = $inventoryImageData->fetch(PDO::FETCH_OBJ)) {
            $imageModel = new Image();
            $imageModel->load($row->image_id);

            // Handle Noverlay Special
            $noverlay = $imageModel->getData('filename_noverlay');
            $imageData[] = array(
                'identifier' => CompactHelper::shorten($imageModel->id),
                'url' => ImageHelper::getImageUrl($imageModel->filename),
                'noverlay' => (!empty($noverlay) ? ImageHelper::getImageUrl($noverlay) : ''),
                'position' => $row->position
            );
        }

        return $imageData;

    }

    public function getFiles()
    {
        //TODO renew function for laravel

        $query = new Db_Query('inventory_file');

        $query->add('inventory_id', $this->id);
        $query->order('position', 'asc');

        $inventoryFileData = $query->doSelect();

        $fileData = array();
        while ($row = $inventoryFileData->fetch(PDO::FETCH_OBJ)) {
            $fileData[] = array(
                'file_id' => $row->file_id,
                'inventory_id' => $row->inventory_id,
                'position' => $row->position
            );
        }

        return $fileData;

    }

    public function setLongitude($longitude)
    {
        //TODO renew function for laravel
        $latitude = $this->getLatitude();
        if (empty($latitude) || is_null($latitude)) {
            $latitude = 0;
        }
        $this->geolocation = GeospatialHelper::ToWKB($latitude, $longitude);
    }

    public function duplicate()
    {

        $newModel = clone($this);

        $newModel->_origData = null;

        unset($newModel->_data['inventory_id']);
        $newModel->_data['stock'] = $newModel->_data['stock'] . '-' . time();
        unset($newModel->_data['created_at']);
        unset($newModel->_data['updated_at']);
        unset($newModel->_data['vin']);

        return $newModel;

    }
}
