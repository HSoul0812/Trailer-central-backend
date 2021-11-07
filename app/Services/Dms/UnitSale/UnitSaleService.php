<?php

namespace App\Services\Dms\UnitSale;

use App\Models\User\User;
use App\Repositories\Dms\QuoteRepository;

/**
 * Class UnitSaleService
 *
 * @package App\Services\Dms\UnitSale
 */
class UnitSaleService implements UnitSaleServiceInterface
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param array $params
     * @param int $dealerId
     * @return bool
     */
    public function bulkArchive(array $params, int $dealerId): bool
    {
        return $this->quoteRepository->bulkArchive($dealerId, $params['quote_ids']);
    }
}
