<?php


namespace App\Transformers\Dms\ServiceOrder;


use App\Models\CRM\Dms\ServiceOrder\PartItem;
use App\Transformers\Parts\PartsTransformer;
use League\Fractal\TransformerAbstract;

class PartItemTransformer extends TransformerAbstract
{
    public $availableIncludes = [
        'part'
    ];

    public function transform(PartItem $item)
    {
        return [
            'id' => (int)$item->id,
            'part_id' => $item->part_id,
            'po_id' => $item->po_id,
            'bin_id' => $item->bin_id,
            'qty' => (int)$item->qty,
            'price' => (float)$item->price,
            'notes' => $item->notes,
        ];
    }

    public function includePart(PartItem $item)
    {
        return $this->item($item->part, new PartsTransformer());
    }

}
