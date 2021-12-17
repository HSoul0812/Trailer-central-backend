<?php

declare(strict_types=1);

namespace App\Repositories;

interface CrudCommonWriterRepositoryInterface
{
    /**
     * Create a single record.
     *
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * Updates a single record by primary key.
     *
     * @param mixed $id
     *
     *  @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($id, array $newAttributes): bool;

    /**
     * Deletes a single record by primary key.
     *
     * @param mixed $id
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete($id): bool;
}
