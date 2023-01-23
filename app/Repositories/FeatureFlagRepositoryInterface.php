<?php

namespace App\Repositories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Collection;

interface FeatureFlagRepositoryInterface
{
    /**
     * @return Collection<FeatureFlag>|FeatureFlag[] list of feature flags indexed by code
     */
    public function getAll(): Collection;

    public function get(string $code): ?FeatureFlag;

    public function isEnabled(string $code): bool;
}
