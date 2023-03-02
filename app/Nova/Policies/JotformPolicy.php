<?php

namespace App\Nova\Policies;

use App\Models\CRM\Leads\Jotform\WebsiteForms;
use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class WebsiteFormsPolicy
 * @package App\Nova\Policies
 */
class JotformPolicy
{
    use HandlesAuthorization;

    private const VALID_ROLES = ['Admin', 'Support'];

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Determine whether the user can view any form.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function viewAny(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can view the form.
     *
     * @param NovaUser|null $user
     * @param WebsiteForms $form
     * @return bool
     */
    public function view(?NovaUser $user, WebsiteForms $form): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can create forms.
     *
     * @param NovaUser $user
     * @return bool
     */
    public function create(NovaUser $user): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can update the form.
     *
     * @param NovaUser $user
     * @param WebsiteForms $form
     * @return bool
     */
    public function update(NovaUser $user, WebsiteForms $form): bool {
        return $user->hasAnyRole(self::VALID_ROLES);
    }

    /**
     * Determine whether the user can delete the form.
     *
     * @param NovaUser $user
     * @param WebsiteForms $form
     * @return bool
     */
    public function delete(NovaUser $user, WebsiteForms $form): bool {
        return false;
    }

    /**
     * Determine whether the user can restore the form.
     *
     * @param NovaUser $user
     * @param WebsiteForms $form
     * @return void
     */
    public function restore(NovaUser $user, WebsiteForms $form): void {
        //
    }

    /**
     * Determine whether the user can permanently delete the form.
     *
     * @param NovaUser $user
     * @param WebsiteForms $form
     * @return void
     */
    public function forceDelete(NovaUser $user, WebsiteForms $form): void {
        //
    }
}

