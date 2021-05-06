<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settings extends Model
{
    /**
     * @var array of setting fields
     */
    const SETTING_FIELDS = [
        'lock_state',
        'inventory_table_color_mode',
        'website_leads_cdk_source_id',
        'website_leads_cdk_error',
        'credit_card_processor',
        'invoice_template',
        'qb_data_retrieve',
        'quote_print_template',
        'quote_print_header_content',
        'quote_mv_no_visible',
        'quote_plate_no_visible',
        'label_printer',
        'receipt_printer',
        'only_send_inventory_with_bill',
        'salesperson_visible',
        'service_price_editable',
        'quote_inspection_info',
        'quote_unit_description_visible',
        'decimals'
    ];


    const TABLE_NAME = 'dealer_admin_settings';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'setting',
        'setting_value'
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get Dealer
     * 
     * @return BelongsTo
     */
    public function dealer(): BelongsTo {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
}