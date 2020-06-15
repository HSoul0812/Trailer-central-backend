<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model {
    
    protected $table = 'website_redirect';
    
    protected $primaryKey = 'identifier';
    
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from',
        'to',
        'code',
        'note',
        'website_id'
    ];
    
}
