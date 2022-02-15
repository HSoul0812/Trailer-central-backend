<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\DealerUserPermission;

class DealerPermissionsTransformer extends TransformerAbstract 
{    
    
    public function transform(DealerUserPermission $permission)
    {                             
	return [
             'feature' => $permission->feature,
             'permission_level' => $permission->permission_level
        ];
    }
}
