<?php

namespace App\Rules\Website\Image;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use function PHPUnit\Framework\greaterThanOrEqual;

class IsAfterDate implements Rule
{
    /**
     * @var string|null
     */
    private $startDate;

    /**
     * Create a new rule instance.
     *
     * @param string|null $startDate
     */
    public function __construct(?string $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($this->startDate) {
            try {
                $current = Carbon::parse($value);
                $startDate = Carbon::parse($this->startDate);
                return $current->isSameDay($startDate) || $current->isAfter($startDate);
            } catch (Exception $exception) {
                return false;
            }
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
        return 'expires_at must not be before starts_from';
    }
}
