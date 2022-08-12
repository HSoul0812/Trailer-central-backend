<?php

namespace App\Http\Controllers\v1\Dms\Quote;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Dms\Quotes\UpdateQuoteSettingsRequest;
use App\Services\Dms\Quote\QuoteSettingServiceInterface;
use Dingo\Api\Http\Response;

class QuoteSettingController extends RestfulController
{
    /** @var QuoteSettingServiceInterface */
    protected $quoteSettingService;

    public function __construct(QuoteSettingServiceInterface $quoteSettingService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['updateDealerSetting']);

        $this->quoteSettingService = $quoteSettingService;
    }

    /**
     * We need to use a different name from 'update' here because we need to
     * include the 'id' in the parameter if we want to use the 'update' method name
     *
     * @param UpdateQuoteSettingsRequest $updateQuoteSettingsRequest
     * @return Response
     */
    public function updateDealerSetting(UpdateQuoteSettingsRequest $updateQuoteSettingsRequest)
    {
        $this->quoteSettingService->update(
            $updateQuoteSettingsRequest->validated(),
            $updateQuoteSettingsRequest->input('dealer_id')
        );

        return $this->response->array(['message' => 'success']);
    }
}
