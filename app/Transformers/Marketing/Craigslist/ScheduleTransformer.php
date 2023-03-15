<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Models\Inventory\Inventory;
use App\Traits\CompactHelper;
use League\Fractal\TransformerAbstract;

/**
 * Class QueueTransformer
 *
 * @package App\Transformers\Marketing\Craigslist
 */
class ScheduleTransformer extends TransformerAbstract
{
    /**
     * @param Queue $queue
     * @return array
     */
    public function transform(Queue $queue): array
    {
        $startAndEndTimes = $this->getStartAndEndTimes($queue);
        $itemStyling = $this->itemStyling($queue);

        return [
            "id" => "queue_{$queue->queue_id}",
            "queue_id" => $queue->queue_id,
            "session_id" => $queue->session_id,
            "inventory" => CompactHelper::shorten($queue->inventory_id),
            "inventory_id" => $queue->inventory_id,
            "archived" => boolval($queue->is_archived),
            "title" => $queue->title,
            "stock" => $queue->stock,
            "price" => $queue->price,
            "manufacturer" => $queue->make,
            "category" => $queue->category_label,
            "image" => $queue->primary_image,
            "allDay" => boolval($startAndEndTimes['allDay']),
            "start" => $startAndEndTimes['start'],
            "end" => $startAndEndTimes['end'],
            "error" => ($queue->status === 'error') ? $queue->text_status : '',
            "text_status" => $queue->text_status,
            "durationEditable" => false,
            "className" => $itemStyling['className'],
            "editable" => $itemStyling['editable'],
            "color" => $itemStyling['color']
        ];
    }

    private function getStartAndEndTimes(Queue $queue): array
    {
        $timing = explode(" ", $queue->session_scheduled);
        if (empty($queue->session_scheduled)) {
            $timing = explode(" ", $queue->session_started);
        }

        $queueDate = date('Y-m-d', $queue->time);
        $queueTime = date('H:i:s', $queue->time);

        if (!empty($timing[0])) {
            $queueDate = $timing[0];
        }
        if (!empty($timing[1])) {
            $queueTime = $timing[1];
        }

        $startDate = $queueDate . 'T' . $queueTime . '+00:00';

        // Set End Time
        $endTime = strtotime($queueDate . ' ' . $queueTime) + (60 * 30);
        $endDate  = date('Y-m-d', $endTime);
        $endDate .= 'T' . date('H:i:s', $endTime);
        $endDate .= '+00:00';

        return [
            'allDay' => false,
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    private function itemStyling(Queue $queue): array
    {
        $className = '';
        $editable = false;
        $color = '';

        if (!empty($queue->edits)) {
            $className = 'edit';
            $color = 'darkolivegreen';
        } elseif (!empty($queue->deleted)) {
                $className = 'deleted';
                $color = 'black';
        } elseif (!empty($queue->deleting)) {
            $className = 'deleting';
            $color = 'darkred';
        } elseif ($queue->status === 'error' || $queue->status === 'canceled') {
            if ($queue->state === 'missing-data' ||
                $queue->session->state === 'missing-data' ||
                strpos('invalid-', $queue->session->state) !== FALSE) {
                $className = 'data';
                $color = 'orangered';
            } elseif ($queue->session->state === 'manual-stop' || $queue->staus === 'canceled') {
                $className = 'stopped';
                $color = 'gray';
            } else {
                $className = 'error';
                $color = 'red';
            }
        } elseif ($queue->status === 'pending-billing') {
            $className = 'billing';
            $color = 'purple';
        } elseif ($queue->status === 'done') {
            if(!empty($queue->parameters->autoPost)) {
                $className = 'autopost';
                $color = 'blue';
            } else {
                $className = 'completed';
                $color = 'green';
            }
        } else {
            if ($queue->inventory->status === Inventory::STATUS_SOLD) {
                $className = 'sold';
                $color = 'brown';
            } else {
                $className = 'scheduled';
                $editable = true;
            }
        }

        return [
            'className' => $className,
            'editable' => $editable,
            'color' => $color
        ];
    }
}
