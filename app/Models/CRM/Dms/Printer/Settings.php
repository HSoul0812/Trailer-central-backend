<?php

namespace App\Models\CRM\Dms\Printer;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Settings
 * 
 * @package App\Models\CRM\Dms\Printer
 */
class Settings extends Model
{
    
    public $timestamps = false;
    
    protected $table = "dealer_printer_settings";
    
    
    protected $fillable = [
        'dealer_id',
        'label_width',
        'label_height',
        'label_printer_dpi',
        'label_orientation',
        'barcode_width',
        'barcode_height',
        'sku_price_font_size',
        'sku_price_x_position',
        'sku_price_y_position',
        'barcode_x_position',
        'barcode_y_position'
    ];
    
    /**
     * Get Dealer
     *
     * @return HasOne
     */
    public function dealer() : HasOne {
        return $this->hasOne(User::class, 'dealer_id', 'dealer_id');
    }
}
