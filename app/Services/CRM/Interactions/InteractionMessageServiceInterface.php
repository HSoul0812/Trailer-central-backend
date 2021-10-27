<?php

namespace App\Services\CRM\Interactions;

/**
 * Interface InteractionMessageServiceInterface
 * @package App\Services\CRM\Interactions
 */
interface InteractionMessageServiceInterface
{
    public function bulkUpdate(array $params): bool;
}
