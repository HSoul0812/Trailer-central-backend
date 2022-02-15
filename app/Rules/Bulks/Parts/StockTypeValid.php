<?php

declare(strict_types=1);

namespace App\Rules\Bulks\Parts;

use App\Repositories\Dms\StockRepository;
use Illuminate\Contracts\Validation\Rule;

class StockTypeValid implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param string $value the stock type
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $validStockType = [
            StockRepository::STOCK_TYPE_INVENTORIES,
            StockRepository::STOCK_TYPE_PARTS,
            StockRepository::STOCK_TYPE_MIXED
        ];

        return in_array($value, $validStockType);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The selected :attribute is invalid.';
    }
}
