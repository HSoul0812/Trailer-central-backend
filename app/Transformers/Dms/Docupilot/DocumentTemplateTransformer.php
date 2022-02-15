<?php

namespace App\Transformers\Dms\Docupilot;

use App\Models\CRM\Dms\Docupilot\DocumentTemplates;
use League\Fractal\TransformerAbstract;

/**
 * Class DocumentTemplateTransformer
 * @package App\Transformers\Dms\Docupilot
 */
class DocumentTemplateTransformer extends TransformerAbstract
{
    /**
     * @param DocumentTemplates $item
     * @return array
     */
    public function transform(DocumentTemplates $item): array
    {
        return [
            'id' => $item->id,
            'template_id' => $item->template_id,
            'dealer_id' => $item->dealer_id,
            'type_quote' => $item->type_quote,
            'type_deal' => $item->type_deal,
            'type_service' => $item->type_service,
            'type' => $item->type,
        ];
    }
}
