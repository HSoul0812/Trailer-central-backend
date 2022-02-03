<?php

namespace App\Http\Requests\Feed;

use App\Http\Requests\Request; 

/**
 * Class UpdateAtwInventoryRequest
 * @package App\Http\Requests\Feed
 */
class UpdateAtwInventoryRequest extends Request
{
    /**
     * @var array
     */
    protected $rules = [
        '*.source' => 'required|string|in:pj,btt,bttw,bwt,cmtb,olt,pjt,pjtb,tt,ttcom,wcd',
        '*.dealer.id' => 'required|string',
        '*.vin' => 'required|string',
        '*.photos' => 'array',
        '*.description' => 'string',
        '*.msrp' => 'numeric',
        '*.price' => 'numeric'
    ];
}
