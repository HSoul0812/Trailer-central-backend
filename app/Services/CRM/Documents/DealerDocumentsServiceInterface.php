<?php

namespace App\Services\CRM\Documents;

// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;

interface DealerDocumentsServiceInterface {

    /**
     * @param array $params
     * @return Collection
     */
    public function create(array $params): Collection;

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): bool;
}