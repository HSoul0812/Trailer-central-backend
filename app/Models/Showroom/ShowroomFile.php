<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Model;

class ShowroomFile extends Model {
    
    protected $primaryKey = 'showroom_file_id';
    
    protected $table = 'showroom_file';
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'showroom_id',
        'src',
        'name'
    ];
    
}
