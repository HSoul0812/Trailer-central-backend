<?php

namespace App\Models\Inventory;

use App\Models\Interactions\DealerUpload;
use App\Models\User\Dealer;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;

class InventoryImage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_image';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image_id',
        'inventory_id',
        'position',
        'is_secondary',
        'was_manually_added'
    ];

    static function getDefaultImage($inventory_id) {
        $query = new Db_Query('inventory_image');
        $query->add('inventory_id', $inventory_id);
        $query->order('position');
        $imageData = $query->doSelect();

        $row = $imageData->fetch(PDO::FETCH_OBJ);

        if($row) {
            $imageModel = new Model_Image();
            $imageModel->load($row->image_id);

            if($imageModel->hasData()) {
                return $imageModel;
            }
        } else {
            return null;
        }
    }

    static function getNextInventoryImagePosition($inventory_id) {
        $query = new Db_Query('inventory_image');
        $query->add('inventory_id', $inventory_id);
        $query->order('position', 'desc');
        $imageData = $query->doSelect();

        $row = $imageData->fetch(PDO::FETCH_OBJ);

        if($row) {
            return intval($row->position + 1);
        } else {
            return 1;
        }
    }
}
