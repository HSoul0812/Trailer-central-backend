<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * Class QueueTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class QueueTransformer extends TransformerAbstract
{
    /**
     * @param Queue $queue
     * @return array
     */
    public function transform(Queue $queue): array
    {
        return [
            'dealer_id' => $queue->dealer_id,
            'session_id' => $queue->session_id,
            'queue_id' => $queue->queue_id,
            'parent_id' => $queue->parent_id,
            'profile_id' => $queue->profile_id,
            'inventory_id' => $queue->inventory_id,
            'status' => $queue->status,
            'state' => $queue->state,
            'title' => $queue->title,
            'stock' => $queue->stock,
            'price' => $queue->price,
            'image' => $queue->primary_image,
            'parameters' => $queue->parameters,
            'time' => strtotime($queue->session->session_scheduled),
            'queue_time' => $queue->time,
            'queued_at' => Carbon::createFromTimestamp($queue->time)->toDateTimeString()
        ];
    }
}