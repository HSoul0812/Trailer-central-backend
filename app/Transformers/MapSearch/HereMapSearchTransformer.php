<?php


namespace App\Transformers\MapSearch;


use League\Fractal\Manager;
use League\Fractal\TransformerAbstract;

class HereMapSearchTransformer extends TransformerAbstract
{
    public function transform($searchResult): array {
        $itemTransformer = new HereMapSearchItemTransformer();
        $data = [];
        foreach($searchResult->items as $item) {
            $data[] = $itemTransformer->transform($item);
        }
        return $data;
    }
}
