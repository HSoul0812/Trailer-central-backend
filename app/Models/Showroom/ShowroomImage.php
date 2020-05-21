<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Model;

class ShowroomImage extends Model {
    
    protected $primaryKey = 'showroom_image_id';
    
    protected $table = 'showroom_image';
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'showroom_id',
        'src',
        'is_floorplan'
    ];
}
