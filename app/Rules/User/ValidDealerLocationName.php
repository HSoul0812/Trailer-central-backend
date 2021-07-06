<?php

declare(strict_types=1);

namespace App\Rules\User;

use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Rules\ValidAuthUser;

/**
 *  Validates that given location name is unique.
 */
class ValidDealerLocationName extends ValidAuthUser
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
        if (!parent::passes($attribute, $value, $parameters)) {
            return false;
        }

        /** @var DealerLocationRepositoryInterface $repo */
        $repo = app()->make(DealerLocationRepositoryInterface::class);

        $dealerLocationId = $parameters[0] ? (int)$parameters[0] : null;

        return !$repo->existByName($value, $this->user->dealer_id, $dealerLocationId);
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
