<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
    /**
     * Stop Processing Text Repository
     *
     * @param array $params
     * @return Stop
     */
    public function stop($params);

    /**
     * @param array $params
     * @return mixed
     */
    public function bulkUpdate(array $params): bool;
}
