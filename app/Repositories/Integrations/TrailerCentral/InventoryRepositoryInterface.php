<?php

declare(strict_types=1);

namespace App\Repositories\Integrations\TrailerCentral;

use Carbon\Carbon;

/**
 * Describes inventory integration repository.
 */
interface InventoryRepositoryInterface extends SourceRepositoryInterface
{
    public function expireItems(Carbon $from, Carbon $to);
}
