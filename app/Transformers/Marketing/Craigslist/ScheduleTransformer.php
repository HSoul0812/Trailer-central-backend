<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
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
            "queue_id" => "queue_{$queue->queue_id}",
            "title" => "Inv #" . $queue->inventory->inventory_id,
            "inventory_id" => $queue->inventory->inventory_id,
            "session_id" => $queue->session_id,
            "archived" => boolval($queue->inventory->is_archived),
            "allDay" => boolval($startAndEndTimes['allDay']),
            "start" => $startAndEndTimes['start'],
            "end" => $startAndEndTimes['end'],
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

        $allDayConfig = config('marketing.cl.settings.scheduler.allDay');
        $queueDate = date('Y-m-d', $queue->time);
        $queueTime = date('H:i:s', $queue->time);

        if (!empty($timing[0])) {
            $queueDate = $timing[0];
        }
        if (!empty($timing[1])) {
            $queueTime = $timing[1];
        }

        $startDate = $queueDate;

        if (!$allDayConfig) {
            $startDate .= 'T' . $queueTime . '+00:00';
        }

        // Set End Time
        $endTime = strtotime($queueDate . ' ' . $queueTime) + (60 * 30);
        $endDate  = date('Y-m-d', $endTime);
        $endDate .= 'T' . date('H:i:s', $endTime);
        $endDate .= '+00:00';

        return [
            'allDay' => $allDayConfig,
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    private function itemStyling(Queue $queue): array
    {
        $className = '';
        $editable = false;
        $color = '';

        if (!empty($queue->q_status)) {
            $className = 'edit';
            $color = 'darkolivegreen';
        } elseif (!empty($queue->d_status)) {
            if ($queue->d_status === 'done') {
                $className = 'deleted';
                $color = 'black';
            }
        } elseif ($queue->status === 'error' || $queue->status === 'canceled') {
            if ($queue->state === 'missing-data' || $queue->s_state === 'missing-data') {
                $className = 'data';
                $color = 'orangered';
            } elseif ($queue->s_state === 'manual-stop' || $queue->staus === 'canceled') {
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
            $className = 'completed';
            $color = 'green';
        } else {
            if ($queue->i_status == '2') {
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
