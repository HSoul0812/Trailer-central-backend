<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User\User;
use Illuminate\Support\Facades\Auth;

/**
 *  Validates that given attributes are mutually unique.
 */
class ValidAuthUser
{
    /** @var User */
    protected $user;

    /**
     * Runs the validation.
     *
     * @param string $attribute the attribute that being validate
     * @param string $value the value of the attribute (location name)
     * @param array $parameters
     * @return bool true when is valid
     */
    public function passes(string $attribute, string $value, array $parameters = []): bool
    {
        $this->user = Auth::user();

        if ($this->user === null || ($this->user && empty($this->user->dealer_id))) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Dealer must be authenticated';
    }
}
