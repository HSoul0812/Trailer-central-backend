<?php

declare(strict_types=1);

namespace App\Http\Requests\CRM\User;

class GetTimeClockRequest extends TimeClockWithPermissionValidationRequest
{
    /** @var string */
    protected $fromDate;

    /** @var string */
    protected $toDate;

    public function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'employee_id' => 'integer|min:1|required|exists:dealer_employee,id',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => $this->validToDate()
        ];
    }

    public function getFromDate(): ?string
    {
        return $this->input('from_date');
    }

    public function getToDate(): ?string
    {
        return $this->input('to_date');
    }

    private function validToDate(): string
    {
        $formDate = $this->getFromDate();

        return $formDate ?
            sprintf('required_with:from_date|date_format:Y-m-d|after_or_equal:%s', $formDate) :
            'required_with:from_date|date_format:Y-m-d';
    }
}
