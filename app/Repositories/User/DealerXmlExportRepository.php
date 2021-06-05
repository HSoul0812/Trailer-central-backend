<?php

namespace App\Repositories\User;

use App\Repositories\RepositoryAbstract;
use App\Repositories\User\DealerXmlExportRepositoryInterface;
use App\Models\User\DealerXmlExport;

class DealerXmlExportRepository extends RepositoryAbstract implements DealerXmlExportRepositoryInterface
{
    public function get($params) {
        $dealerXmlExport = DealerXmlExport::where('dealer_id', $params['dealer_id'])->first();
        if (empty($dealerXmlExport)) {
            $dealerXmlExport = DealerXmlExport::create(['dealer_id' => $params['dealer_id'], 'export_active' => DealerXmlExport::EXPORT_INACTIVE]);
        }        
        return $dealerXmlExport;
    }
    
    public function updateExport(int $dealerId, bool $exportStatus): DealerXmlExport {
        $dealerXmlExport = $this->get(['dealer_id' => $dealerId]);
        $dealerXmlExport->export_active = $exportStatus;
        $dealerXmlExport->save();
        return $dealerXmlExport;
    }

}
