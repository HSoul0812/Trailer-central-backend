<?php

declare(strict_types=1);

namespace App\Repositories\Dms\Integration;

use App\Exceptions\NotImplementedException;
use App\Models\Dms\Integration\SyncProcess;
use Illuminate\Support\Facades\Date;

class SyncProcessRepository implements SyncProcessRepositoryInterface
{
    public function isNotTheFirstImport(string $name): bool
    {
        return SyncProcess::query()
            ->where('name', $name)
            ->where('status', SyncProcess::STATUS_FINISHED)
            ->exists();
    }

    public function lastByProcessName(string $name): ?SyncProcess
    {
        return SyncProcess::query()
            ->where('name', $name)
            ->where('status', '!=', SyncProcess::STATUS_FAILED)
            ->latest()
            ->first();
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function finishById(int $id, array $meta = []): bool
    {
        return SyncProcess::query()->findOrFail($id)->update([
            'finished_at' => Date::now(),
            'status'      => SyncProcess::STATUS_FINISHED,
            'meta'        => $meta,
        ]);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function failById(int $id, array $meta = []): bool
    {
        return SyncProcess::query()->findOrFail($id)->update([
            'finished_at' => Date::now(),
            'status'      => SyncProcess::STATUS_FAILED,
            'meta'        => $meta,
        ]);
    }

    public function create(array $attributes): SyncProcess
    {
        return SyncProcess::query()->create($attributes);
    }

    /**
     * @param int $primaryKey
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($primaryKey, array $newAttributes): bool
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($primaryKey): bool
    {
        throw new NotImplementedException();
    }
}
