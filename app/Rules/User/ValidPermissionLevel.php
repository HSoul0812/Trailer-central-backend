<?php

namespace App\Rules\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;

/**
 *  Validates that the given permission level is valid
 */
class ValidPermissionLevel
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
        $salesPersonRepo = app(SalesPersonRepositoryInterface::class);
        $dealerLocationRepo = app(DealerLocationRepositoryInterface::class);
        
        if (in_array($value, PermissionsInterface::PERMISSION_LEVELS)) {
            return true;
        }
                
        if ( $salesPersonRepo->get(['sales_person_id' => $value]) ) {
            return true;
        }
        
        if ( $dealerLocationRepo->get(['dealer_location_id' => $value]) ) {
            return true;
        }
        

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Permission level must exist';
    }
}
