<?php


namespace App\Models\CRM\Dms;


use App\Utilities\JsonArrAccess\WithDotAccessibleColumns;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use WithDotAccessibleColumns;

    protected $table = 'dms_settings';

    protected $fillable = [
        "dealer_id",
        "pos_editable_sales_price",
        "pos_show_shipping",
        "labor_price_editable",
        "pos_allow_misc_parts_sales",
        "ro_invoice_create_setting",
        "quickbooks_simple_mode",
        "meta->repairOrder",
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function fillWithMeta(array $attributes)
    {
        // fill as normal
        parent::fill($attributes);

        // set other items
        if (isset($attributes['meta'])) {
            foreach ($attributes['meta'] as $key => $value)  {
                $this->setByName('meta', $key, $value);
            }
        }
    }
}
