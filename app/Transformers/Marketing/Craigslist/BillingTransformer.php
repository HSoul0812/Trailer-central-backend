<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Transaction;
use App\Traits\CompactHelper;
use League\Fractal\TransformerAbstract;

/**
 * Class QueueTransformer
 *
 * @package App\Transformers\Marketing\Craigslist
 */
class BillingTransformer extends TransformerAbstract
{
    public function transform(Transaction $transaction): array
    {
        $startAndEndTimes = $this->getStartAndEndTimes($transaction);

        return [
            "id" => $transaction->getKey(),
            "title" => $transaction->amount,
            "session" => $transaction->session_id,
            "queue" => $transaction->queue_id,
            "inventory" => CompactHelper::shorten($transaction->inventory_id),
            "allDay" => boolval($startAndEndTimes['allDay']),
            "start" => $startAndEndTimes['start'],
            "end" => $startAndEndTimes['end'],
            "durationEditable" => false,
            "editable" => false,
        ];
    }

    private function getStartAndEndTimes(Transaction $transaction): array
    {
        $timing = explode(" ", $transaction->session_scheduled);
        if (empty($transaction->session_scheduled)) {
            $timing = explode(" ", $transaction->session_started);
        }

        $queueDate = date('Y-m-d', $transaction->time);
        $queueTime = date('H:i:s', $transaction->time);

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
}
