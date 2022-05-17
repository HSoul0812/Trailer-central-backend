<?php

namespace App\Repositories\CRM\Text;

use App\Models\CRM\Interactions\TextLog;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * @param string $fromNumber
     * @param string $toNumber
     * @return TextLog|null
     */
    public function findByFromNumberToNumber(string $fromNumber, string $toNumber): Collection;

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
