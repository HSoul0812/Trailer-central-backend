<?php

namespace App\Repositories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Collection;
use Schema;

class FeatureFlagRepository implements FeatureFlagRepositoryInterface
{
    /** @var ?FeatureFlag */
    private $model;

    /** @var Collection<FeatureFlag>|Collection[] list of feature flags indexed by code */
    private static $list;

    public function __construct()
    {
        if (Schema::hasTable(FeatureFlag::getTableName())) {
            $this->model = new FeatureFlag();
        }
    }

    /**
     * @inheritDoc
     */
    public function getAll(): Collection
    {
        // Return an empty collection if the table isn't being created yet
        if ($this->model === null) {
            return new Collection();
        }

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
