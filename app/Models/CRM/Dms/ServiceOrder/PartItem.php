<?php


namespace App\Models\CRM\Dms\ServiceOrder;


use App\Models\Parts\Part;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartItem
 * @package App\Models\CRM\Dms\ServiceOrder
 * @property Part $part
 */
class PartItem extends Model
{
    protected $table = 'dms_part_item';

    public function part()
    {
        return $this->hasOne(Part::class, 'id', 'part_id');
    }
}
