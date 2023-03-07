<?php

namespace App\Services\Dms\Quote;

use App\Repositories\Dms\QuoteSettingRepositoryInterface;

class QuoteSettingService implements QuoteSettingServiceInterface
{
    /** @var QuoteSettingRepositoryInterface */
    protected $quoteSettingRepository;

    public function __construct(QuoteSettingRepositoryInterface $quoteSettingRepository)
    {
        $this->quoteSettingRepository = $quoteSettingRepository;
    }

    public function update(array $params, int $dealerId)
    {
        $params['dealer_id'] = $dealerId;

        $this->quoteSettingRepository->update($params);
    }
}
