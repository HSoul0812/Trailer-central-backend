<?php

namespace App\Transformers\Dms\Quickbooks;

use League\Fractal\TransformerAbstract;

class QuickbookApprovalTransformer extends TransformerAbstract
{

    public function transform($quickbookApproval)
    {   
        return [
            'id' => $quickbookApproval->id,
            'dealer_id' => $quickbookApproval->dealer_id,
        ];
    }
} 