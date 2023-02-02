<?php

namespace App\Events\DealerExports;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Traits\WithGetter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User\User;

class EntityDataExported
{
    use Dispatchable, InteractsWithSockets, SerializesModels, WithGetter;

    /** @var User */
    public $dealer;

    /** @var string */
    public $entityType;

    /** @var string */
    public $filePath;

    public function __construct(User $dealer, string $entityType, string $filePath)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
        $this->filePath = $filePath;
    }
}
