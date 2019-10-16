<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{ 
    
    protected $table = 'parts_v1';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id', 
        'vendor_id',
        'vehicle_specific_id',
        'manufacturer_id',
        'brand_id',
        'type_id',
        'category_id',
        'qb_id',
        'subcategory',
        'title',
        'sku',
        'price',
        'dealer_cost',
        'msrp',
        'weight',
        'weight_rating',
        'description',
        'qty',
        'show_on_website',
        'is_vehicle_specific'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
        
    public function brand()
    {
        return $this->belongsTo('App\Models\Parts\Brand');
    }
    
    public function type()
    {
        return $this->belongsTo('App\Models\Parts\Type');
    }
    
    public function vendor()
    {
        return $this->belongsTo('App\Models\Parts\Vendor');
    }
    
    public function category()
    {
        return $this->belongsTo('App\Models\Parts\Category');
    }
    
    public function manufacturer()
    {
        return $this->belongsTo('App\Models\Parts\Manufacturer');
    }
    
    public function images()
    {
        return $this->hasMany('App\Models\Parts\PartImage');
    }
}
