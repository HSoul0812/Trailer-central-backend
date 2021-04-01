<?php

namespace App\Models\User\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface PermissionsInterface
 * @package App\Models\User
 */
interface PermissionsInterface
{
    /**
     * Features
     */
    const INVENTORY = 'inventory';
    const INTEGRATIONS = 'integrations';
    const MARKETING = 'marketing';
    const PARTS = 'parts';
    const WEBSITE = 'website';
    const CRM = 'crm';
    const FI = 'fi';
    const POS = 'pos';
    const SERVICE = 'service';
    const BACK_OFFICE = 'back_office';

    const FEATURES = [
        self::INVENTORY,
        self::INTEGRATIONS,
        self::MARKETING,
        self::PARTS,
        self::WEBSITE,
        self::CRM,
        self::FI,
        self::POS,
        self::SERVICE,
        self::BACK_OFFICE,
    ];

    /**
     * Permission levels
     */
    const SUPER_ADMIN_PERMISSION = 'super_admin';
    const CAN_SEE_AND_CHANGE_PERMISSION = 'can_see_and_change';
    const CAN_SEE_AND_CHANGE_IMAGES_PERMISSION = 'can_see_and_change_images';
    const CAN_SEE_PERMISSION = 'can_see';
    const CANNOT_SEE_PERMISSION = 'cannot_see';

    const PERMISSION_LEVELS = [
        self::SUPER_ADMIN_PERMISSION,
        self::CAN_SEE_AND_CHANGE_PERMISSION,
        self::CAN_SEE_AND_CHANGE_IMAGES_PERMISSION,
        self::CAN_SEE_PERMISSION,
        self::CANNOT_SEE_PERMISSION,
    ];

    /**
     * @return Collection
     */
    public function getPermissions(): Collection;

    /**
     * @param string $feature
     * @param string $permissionLevel
     * @return bool
     */
    public function hasPermission(string $feature, string $permissionLevel): bool;
}