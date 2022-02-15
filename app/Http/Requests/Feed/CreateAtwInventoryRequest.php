<?php

namespace App\Http\Requests\Feed;

use App\Http\Requests\Request; 

/**
 * Class CreateAtwInventoryRequest
 * @package App\Http\Requests\Feed
 */
class CreateAtwInventoryRequest extends Request
{
    /**
     * @var array
     */
    protected $rules = [
        '*.source' => 'required|string|in:pj,btt,bttw,bwt,cmtb,olt,pjt,pjtb,tt,ttcom,wcd',
        '*.attributes.model' => 'required|string',
        '*.stock_id' => 'required|string',
        '*.attributes.category' => 'required|string',
        '*.dealer.id' => 'required|string',
        '*.vin' => 'required|string',
        '*.photos' => 'array',
        '*.ship_date' => 'date'
    ];
}
