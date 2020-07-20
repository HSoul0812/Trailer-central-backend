<?php
namespace App\Models\Inventory;

use App\Helpers\StringHelper;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\InventoryLead;
use App\Models\CRM\Leads\Lead;
use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory\InventoryImage;
use App\Models\Inventory\Image;
use App\Models\User\User;
use App\Models\Traits\TableAware;

class Inventory extends Model
{    
    use TableAware;
    
    const COLOR_ATTRIBUTE_ID = 11;
    
    const TABLE_NAME = 'inventory';
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'inventory_id';

    protected $fillable = [
        'fp_balance',
        'fp_interest_paid',
        'length',
        'width',
        'height'
    ];
    
    protected $casts = [
        'is_archived' => 'integer',
        'length' => 'float',
        'length_inches' => 'float',
        'width' => 'float',
        'width_inches' => 'float',
        'height' => 'float',
        'height_inches' => 'float',
        'weight' => 'float',
        'true_cost' => 'float',
        'price' => 'float',
        'msrp' => 'float',
        'gvwr' => 'float'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'inventory_id', 'inventory_id', InventoryLead::class);
    }

    public function attribute()
    {
        return $this->hasManyThrough(Attribute::class, 'eav_attribute_value', 'attribute_id', 'inventory_id');
    }

    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function floorplanPayments()
    {
        return $this->hasMany('App\Models\Inventory\Floorplan\Payment', 'inventory_id', 'inventory_id');
    }
    
    public function images()
    {
        return $this->hasManyThrough(Image::class, InventoryImage::class, 'inventory_id', 'image_id', 'inventory_id', 'image_id');
    }
    
    public function getColorAttribute()
    {
        $color = self::select('*')
                    ->join('eav_attribute_value', 'inventory.inventory_id', '=', 'eav_attribute_value.inventory_id')
                    ->where('inventory.inventory_id', $this->inventory_id)
                    ->where('eav_attribute_value.attribute_id', self::COLOR_ATTRIBUTE_ID)
                    ->first();
        if ($color) {
            return $color->value;
        }
        
        return null;
    }

    public function __toString() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $url = '/';
        $url .= StringHelper::superSanitize($this->title, '-');
        $url .= '-' . CompactHelper::shorten($this->inventory_id);

        $url .= '.html';

        return $url;
    }
    
    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
