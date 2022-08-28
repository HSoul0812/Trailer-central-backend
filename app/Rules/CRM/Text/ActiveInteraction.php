<?php

namespace App\Rules\CRM\Text;

use App\Models\CRM\Text\Number;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class IsActiveInteraction
 * @package App\Rules\CRM\Text
 */
class ActiveInteraction implements Rule
{
    /**
     * @var NumberRepositoryInterface
     */
    private $numberRepository;

    /**
     * @param NumberRepositoryInterface $numberRepository
     */
    public function __construct(NumberRepositoryInterface $numberRepository)
    {
        $this->numberRepository = $numberRepository;
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validate($attribute, $value, $parameters): bool
    {
        return $this->numberRepository->activeTwilioNumber($value, $parameters[0]) instanceof Number;
    }

    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute is not valid.';
    }
}
