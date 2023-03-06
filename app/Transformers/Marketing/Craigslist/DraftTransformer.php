<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Draft;
use League\Fractal\TransformerAbstract;

/**
 * Class DraftTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class DraftTransformer extends TransformerAbstract
{
    /**
     * @param Draft $draft
     * @return array
     */
    public function transform(Draft $draft): array
    {
        return [
            'clapp_draft_id' => $draft->id,
            'profile_id' => $draft->profile_id,
            'session_id' => $draft->session_id,
            'queue_id' => $draft->queue_id,
            'inventory_id' => $draft->inventory_id,
            'time' => strtotime($draft->added),
            'created_at' => $draft->added,
            'drafted_at' => $draft->drafted,
            'response' => $draft->response,
            'username' => $draft->username,
            'title' => $draft->current_title,
            'price' => $draft->current_price,
            'area' => $draft->area,
            'subarea' => $draft->subarea,
            'category' => $draft->category,
            'preview' => $draft->preview
        ];
    }
}