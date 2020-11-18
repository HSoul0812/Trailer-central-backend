<?php


namespace App\Listeners\Parts;


use App\Events\Parts\PartQtyUpdated;

/**
 * Class PartReindexNotification
 *
 * Reindex part on qty change
 *
 * @package App\Listeners\Parts
 */
class PartReindexNotification
{
    public function handle(PartQtyUpdated $event)
    {
        if ($event->part) {
            $event->part->searchable();
        }
    }
}
