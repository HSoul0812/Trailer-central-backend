<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\DealerXmlExport;

class DealerXmlExportTransformer extends TransformerAbstract 
{
    public function transform(DealerXmlExport $user)
    {                
	return [
            'export_active' => $user->export_active,
            'xml_export_url' => $this->getXmlExportUrl($user)
        ];
    }
    
    private function getXmlExportUrl(DealerXmlExport $user)
    {
        $sharedKey = '3.141592653589793238462643383279' . '.' . $user->dealer_id;
        return 'http://feed.trailercentral.com/dealer/inventory/'.hash('sha256', $sharedKey) . sprintf("%04x", $user->dealer_id) . '/xml';
    }
}
