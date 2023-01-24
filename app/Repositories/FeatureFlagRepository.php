<?php

namespace App\Repositories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Collection;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    /** @var FeatureFlag */
    private $model;

    /** @var Collection<FeatureFlag>|Collection[] list of feature flags indexed by code */
    private static $list;

    public function __construct(FeatureFlag $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        if ($this->isEmpty()) {
            self::$list = $this->model->newQuery()->get()->keyBy('code');
        }

        return self::$list;
    }

    public function get(string $code): ?FeatureFlag
    {
        return $this->getAll()->get($code);
    }

    /**
     * @inheritDoc
     */
    public function set(FeatureFlag $feature): void
    {
        $this->getAll(); // to ensure there is a collection

        self::$list->offsetSet($feature->code, $feature);
    }

    public function isEnabled(string $code): bool
    {
        /** @var FeatureFlag|null $feature */
        $feature = $this->getAll()->get($code);

        return $feature->is_enabled ?? false;
    }

    protected function isEmpty(): bool
    {
        return is_null(self::$list);
    }
}
