<?php

declare(strict_types=1);

namespace App\Repositories\User\Integration\Specific;

trait WithUsedSlotsGetter
{
    /**
     * Gets the specifics values for the dealer integration
     *
     * @param array $params
     * @return array{used_slots: int}
     */
    public function get(array $params): array
    {
        return [
            'used_slots' => $this->getUsedSlotsByDealerId($params['dealer_id'] ?? null)
        ];
    }

    abstract protected function getUsedSlotsByDealerId(?int $dealerId): int;
}
