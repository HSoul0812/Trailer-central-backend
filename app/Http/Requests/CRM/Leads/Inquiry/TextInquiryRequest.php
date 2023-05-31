<?php

namespace App\Http\Requests\CRM\Leads\Inquiry;

use App\Http\Requests\Request;
use App\Rules\CRM\Leads\BannedLeadTextsRule;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Repositories\User\DealerLocationRepositoryInterface;

class TextInquiryRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|exists:dealer,dealer_id',
            'website_id' => 'required|website_exists',
            'dealer_location_id' => 'required|dealer_location_inquiry_valid',
            'inventory_id' => 'nullable|exists:inventory,inventory_id,dealer_id,' . $this->dealer_id,
            'phone_number' => 'required|min:10|phone:US,CA,CL,mobile',
            'sms_message' => ['required', new BannedLeadTextsRule()],
            'customer_name' => 'required',
            'inventory_name' => 'nullable|string',
            'cookie_session_id' => 'nullable|string',
            'referral' => 'required',
            'is_from_classifieds' => 'required|boolean'
        ];
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        $dealerLocationRepo = app(DealerLocationRepositoryInterface::class);
        $country = $dealerLocationRepo->getCountryById($this->dealer_location_id);

        $this->merge([
            'phone_number' => PhoneNumber::make($this->phone_number, $country)->formatE164()
        ]);
    }
}
