<?php

namespace App\Services\Dms\UnitSale;

use App\Models\User\User;
use App\Repositories\Dms\QuoteRepository;
use App\Services\BaseService;

/**
 * Class UnitSaleService
 *
 * @package App\Services\Dms\UnitSale
 */
class UnitSaleService extends BaseService
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

    public function resource(): string
    {
        return '';
    }

    /**
     * @param array $params
     * @param User $user
     *
     * @return bool
     */
    public function bulkArchive(array $params, User $user): bool
    {
        $whereInCondition = [
            'field' => 'id',
            'values' => $params['quote_ids'],
        ];

        $whereCondition = [
            'dealer_id' => $user->dealer_id,
        ];

        $quotes = $this->quoteRepository->fetchAll([], [], $whereCondition, [$whereInCondition]);

        if ($quotes->count() > 0) {
            $whereInCondition['values'] = $quotes->pluck('id');

            return $this->quoteRepository->bulkUpdate([
                'is_archived' => false,
            ], $whereCondition, [$whereInCondition]);
        }

        return false;
    }
}
