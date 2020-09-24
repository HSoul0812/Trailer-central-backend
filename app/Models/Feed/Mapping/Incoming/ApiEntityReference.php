<?php

namespace App\Models\Feed\Mapping\Incoming;

use Illuminate\Database\Eloquent\Model;

class ApiEntityReference extends Model {

    protected $table = 'api_entity_reference';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'api_entity_reference_id';

    public $timestamps = false;
    
    const LOADTRAIL_API_KEY = 'lt';
    const PJ_API_KEY = 'pj';
    const UTC_API_KEY = 'utc';
    const LGS_API_KEY = 'lgs';
    const NOVAE_API_KEY = 'novae';
    const LAMAR_API_KEY = 'lamar';
    const NORSTAR_API_KEY = 'norstar';    

    protected $fillable = [
        'entity_id',
        'reference_id',
        'entity_type',
        'api_key'
    ];
}
