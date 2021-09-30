<?php

namespace App\Repositories\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface InteractionLeadRepositoryInterface
 * @package App\Repositories\CRM\Interactions
 */
interface InteractionMessageRepositoryInterface extends Repository
{
    /**
     * @param array $params
     * @return array
     */
    public function search(array $params): array;

    /**
     * @return LengthAwarePaginator|null
     */
    public function getPaginator(): ?LengthAwarePaginator;

    /**
     * @param array $params
     * @return InteractionMessage
     */
    public function searchable(array $params): InteractionMessage;
}
