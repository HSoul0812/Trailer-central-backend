<?php

declare(strict_types=1);

namespace App\Repositories\Inventory\CustomOverlay;

use App\Exceptions\NotImplementedException;
use App\Models\Inventory\CustomOverlay;
use Illuminate\Database\Eloquent\Collection;

class CustomOverlayRepository implements CustomOverlayRepositoryInterface
{
    /** @var CustomOverlay */
    private $model;

    public function __construct(CustomOverlay $model)
    {
        $this->model = $model;
    }

    public function create($params): CustomOverlay
    {
        return $this->model->create($params);
    }

    public function update($params): bool
    {
        throw new NotImplementedException(sprintf('update method is not implemented yet on %s', __METHOD__));
    }

    public function upsert(array $params): bool
    {
        if (empty($params['dealer_id'])) {
            throw new \InvalidArgumentException("'dealer_id' is required");
        }

        if (empty($params['name'])) {
            throw new \InvalidArgumentException("'name' is required");
        }

        $model = $this->get($params);

        if ($model) {
            return $model->update($params);
        }

        return (bool)$this->create($params);
    }

    public function get($params): ?CustomOverlay
    {
        if (empty($params['dealer_id'])) {
            throw new \InvalidArgumentException("'dealer_id' is required");
        }

        if (empty($params['name'])) {
            throw new \InvalidArgumentException("'name' is required");
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->model::query()
            ->where('dealer_id', $params['dealer_id'])
            ->where('name', $params['name'])
            ->first();
    }

    public function delete($params): bool
    {
        throw new NotImplementedException(sprintf('delete method is not implemented yet on %s', __METHOD__));
    }

    public function getAll($params): Collection
    {
        if (empty($params['dealer_id'])) {
            throw new \InvalidArgumentException("'dealer_id' is required");
        }

        return $this->model::query()->where('dealer_id', $params['dealer_id'])->get();
    }
}
