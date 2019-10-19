<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{ 
    protected $table = 'parts_filter';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'is_visible'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
        
}
