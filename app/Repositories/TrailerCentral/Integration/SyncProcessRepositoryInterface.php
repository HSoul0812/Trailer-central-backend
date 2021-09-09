<?php

declare(strict_types=1);

namespace App\Repositories\TrailerCentral\Integration;

use App\Models\TrailerCentral\Integration\SyncProcess;
use App\Repositories\Common\CrudCommonWriterRepositoryInterface;

interface SyncProcessRepositoryInterface extends CrudCommonWriterRepositoryInterface
{
    public function isNotTheFirstImport(string $name): bool;

    public function lastFinishedByProcessName(string $name): ?SyncProcess;

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function finishById(int $id, array $meta = []): bool;

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function failById(int $id, array $meta = []): bool;

    /**
     * Create a single record.
     *
     * @return mixed
     */
    public function create(array $attributes): SyncProcess;

    /**
     * @param int $primaryKey
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($primaryKey, array $newAttributes): bool;

    /**
     * Deletes a single record by primary key.
     *
     * @param int $primaryKey
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete($primaryKey): bool;
}
