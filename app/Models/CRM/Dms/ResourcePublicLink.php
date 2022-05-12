<?php

namespace App\Models\CRM\Dms;

use Illuminate\Database\Eloquent\Model;

class ResourcePublicLink extends Model
{
    protected $table = 'dms_resource_public_links';
    
    public $timestamps = false;
    
    protected $fillable = [
        'resource_type',
        'resource_id',
    ];
}