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

    /**
     * Basically a handy method to test
     *
     * @param  FeatureFlag  $feature
     * @return void
     */
    public function set(FeatureFlag $feature): void;

    public function isEnabled(string $code): bool;
}
