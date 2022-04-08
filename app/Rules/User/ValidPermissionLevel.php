<?php

namespace App\Rules\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Models\User\Interfaces\PermissionsInterface;

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
    public function passes(string $attribute, string $value, array $parameters, $validator): bool
    {
        $otherField = $parameters[0];
        $permissionFeature = data_get($validator->getData(), $otherField);

        // if it is normal permission level which is always in text string
        if (!is_numeric($value)) {
            return in_array($value, PermissionsInterface::PERMISSION_LEVELS);
        }
        // else if permission level is in numeric
        switch ($permissionFeature) {
            case PermissionsInterface::CRM:

                $salesPersonRepo = app(SalesPersonRepositoryInterface::class);
                if ( $salesPersonRepo->get(['sales_person_id' => $value]) ) {
                    return true;
                }

            case PermissionsInterface::LOCATIONS: 

                try {
                    $dealerLocationRepo = app(DealerLocationRepositoryInterface::class);
                    if ($dealerLocationRepo->get(['dealer_location_id' => (int) $value])) {
                        return true;
                    }
                } catch (\Exception $e) {
                    return false;
                }

            default:
                return false;
        }
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
