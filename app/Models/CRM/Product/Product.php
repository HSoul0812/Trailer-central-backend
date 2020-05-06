<?php

namespace App\Models\CRM\Product;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadProduct;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_product';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'product_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "lead_id",
        "product_id",
    ];

    public function lead() {
        return $this->belongsTo(Lead::class, 'product_id', 'product_id', LeadProduct::class);
    }
}
