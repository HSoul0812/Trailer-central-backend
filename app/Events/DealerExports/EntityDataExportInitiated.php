<?php

namespace App\Events\DealerExports;

use App\Traits\WithGetter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User\User;

class EntityDataExportInitiated
{
    use Dispatchable, InteractsWithSockets, SerializesModels, WithGetter;

    /** @var User */
    public $dealer;

    /** @var string */
    public $entityType;

    public function __construct(User $dealer, string $entityType)
    {
        $this->dealer = $dealer;
        $this->entityType = $entityType;
    }
}
