<?php

namespace App\Transformers\CRM\Documents;

use App\Models\CRM\Documents\DealerDocuments;
use League\Fractal\TransformerAbstract;

/**
 * Class DealerDocumentsTransformer
 * @package App\Transformers\CRM\Documents
 */
class DealerDocumentsTransformer extends TransformerAbstract
{
    /**
     * @param DealerDocuments $dealerDocuments
     * @return array
     */
    public function transform(DealerDocuments $dealerDocuments): array
    {
        return [
            'id' => $dealerDocuments->id,
            'dealer_id' => $dealerDocuments->dealer_id,
            'lead_id' => $dealerDocuments->lead_id,
            'filename' => $dealerDocuments->filename,
            'full_path' => $dealerDocuments->full_path,
            'docusign_path' => $dealerDocuments->docusign_path,
            'docusign_data' => $dealerDocuments->docusign_data,
        ];
    }
}
