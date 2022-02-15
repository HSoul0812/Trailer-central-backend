<?php

namespace App\Rules\Inventory;

use App\Models\CRM\Dms\UnitSale;
use App\Models\User\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class QuotesNotExist
 * @package App\Rules\Inventory
 */
class QuotesNotExist implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $quotesExist = UnitSale::query()->where('inventory_id', $value)->exists();

        return !$user->is_dms_active || !$quotesExist;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Can\'t delete inventory linked to quotes. Inventory ID - :attribute';
    }
}
