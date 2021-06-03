<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class AutoImportSettingsTransformer extends TransformerAbstract 
{        
    public function transform(User $user)
    {                
	return [
            'default_description' => $user->default_description,
            'use_description_in_feed' => $user->use_description_in_feed,
            'auto_import_hide' => $user->auto_import_hide,
            'import_config' => $user->import_config,
            'auto_msrp' => $user->auto_msrp,
            'auto_msrp_percent' => $user->auto_msrp_percent
        ];
    }
}
