<?php

namespace App\Domains\DealerExports;
use App\Models\User\User;

class ExportManagerAction
{
    protected $dealerId;

    protected $exportActions = [
        VendorsExporterAction::class,
        BrandsExporterAction::class,
    ];

    public function __construct(int $dealerId)
    {
        $this->dealerId = $dealerId;
    }

    public function execute()
    {
        $dealer = User::query()->where('dealer_id', $this->dealerId)->where('type', User::TYPE_DEALER)->firstOrFail();


    }
}
