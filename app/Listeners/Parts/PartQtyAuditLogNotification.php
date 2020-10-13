<?php


namespace App\Listeners\Parts;


use App\Events\Parts\PartQtyUpdated;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class PartQtyAuditLogNotification
 *
 * @package App\Listeners\Parts
 */
class PartQtyAuditLogNotification
{
    /**
     * @var AuditLogRepositoryInterface
     */
    private $auditLogRepository;

    public function __construct(AuditLogRepositoryInterface $auditLogRepository)
    {
        $this->auditLogRepository = $auditLogRepository;
    }

    public function handle(PartQtyUpdated $event)
    {
        //
        Log::debug("PartQtyAuditLogNotification notified", ['event' => $event]);

        // if part and bin_qty is passed in the event, add an audit log
        if ($event->part && $event->binQuantity) {
            $this->auditLogRepository->create([
                'part_id' => $event->part->id,
                'bin_id' => $event->binQuantity->bin_id,
                'qty' => $event->details['quantity'] ?? 0,
                'balance' => $event->binQuantity->qty ?? 0,
                'description' => $event->details['description'] ?? 'No description',
            ]);
        } else if ($event->part) {
            $this->handlePartAllBins($event);
        }
    }

    private function handlePartAllBins(PartQtyUpdated $event)
    {
        if ($event->part->bins && count($event->part->bins) > 0) {
            foreach ($event->part->bins as $bin) {
                $this->auditLogRepository->create([
                    'part_id' => $event->part->id,
                    'bin_id' => $bin->bin_id,
                    'qty' => 0, // probably an edit of qty not a transaction
                    'balance' => $bin->qty ?? 0,
                    'description' => $event->details['description'] ?? 'No description',
                ]);

            }
        }
    }

}
