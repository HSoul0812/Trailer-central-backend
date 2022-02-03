<?php


namespace App\Listeners\Ecommerce;


use App\Events\Ecommerce\QtyUpdated;
use App\Repositories\Parts\Textrail\PartRepository;

class PartQtyReducer
{
    /** @var PartRepository */
    private $textRailPartRepository;

    /**
     * PartQtyReducer constructor.
     * @param PartRepository $textRailPartRepository
     */
    public function __construct(PartRepository $textRailPartRepository)
    {
        $this->textRailPartRepository = $textRailPartRepository;
    }


    public function handle(QtyUpdated $partQtyUpdatedEvent)
    {
        $part = $this->textRailPartRepository->getById($partQtyUpdatedEvent->getPartId());

        if ($part) {
            $part->qty = ($part->qty - $partQtyUpdatedEvent->getQuantity() >= 0) ? $part->qty - $partQtyUpdatedEvent->getQuantity() : 0;
            $part->save();
        }
    }
}