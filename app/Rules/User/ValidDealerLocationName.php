<?php

declare(strict_types=1);

namespace App\Rules\User;

use App\Repositories\User\DealerLocationRepositoryInterface;

/**
 *  Validates that given location name is unique.
 */
class ValidDealerLocationName
{
    /**
     * Runs the validation.
     *
     * @param string $attribute the attribute that being validate
     * @param string $value the value of the attribute (location name)
     *
     * @return bool true when is valid
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function passes(string $attribute, string $value, array $parameters = []): bool
    {
        /** @var DealerLocationRepositoryInterface $repo */
        $repo = app()->make(DealerLocationRepositoryInterface::class);

        $dealerId = $parameters[0] ? (int)$parameters[0] : null;
        $dealerLocationId = $parameters[1] ? (int)$parameters[1] : null;

        return !$repo->existByName($value, $dealerId, $dealerLocationId);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Dealer Location must be unique';
    }
}
