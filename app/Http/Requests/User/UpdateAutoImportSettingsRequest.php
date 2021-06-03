<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User\User;

class UpdateAutoImportSettingsRequest extends Request
{    
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'default_description' => 'required',
        'use_description_in_feed' => 'required|boolean',
        'auto_msrp' => 'required|boolean',
        'auto_msrp_percent' => 'required|integer'        
    ];    
    
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['auto_import_hide'] = 'required|in:'.implode(',', array_keys(User::AUTO_IMPORT_HIDE_SETTINGS));
        $this->rules['import_config'] = 'required|in:'.implode(',', User::AUTO_IMPORT_SETTINGS);
    }

}
