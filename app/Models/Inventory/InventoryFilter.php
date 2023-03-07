<?php


namespace App\Models\Inventory;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryFilter
 * @package App\Models\Inventory
 *
 * @property int $id
 * @property string $attribute
 * @property string $label
 * @property string $type
 * @property boolean $is_eav
 * @property int $position
 * @property string $sort
 * @property string $sort_dir
 * @property string $prefix
 * @property string $suffix
 * @property int $step
 * @property string $dependancy
 * @property boolean $is_visible
 * @property string $db_field
 */
class InventoryFilter extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_filter';

    public $timestamps = false;

    protected $fillable = [
        'attribute',
        'label',
        'type',
        'is_eav',
        'position',
        'sort',
        'sort_dir',
        'prefix',
        'suffix',
        'step',
        'dependancy',
        'is_visible',
        'db_field'
    ];

    public function websiteConfig()
    {
        return $this->belongsTo(WebsiteConfig::class, 'dependancy', 'key');
    }
}
