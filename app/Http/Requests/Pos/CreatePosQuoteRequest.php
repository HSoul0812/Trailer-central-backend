<?php

declare(strict_types=1);

namespace App\Http\Requests\Pos;

use App\Http\Requests\Request;

/**
 * Class CreatePosQuoteRequest
 *
 * @package App\Http\Requests\Pos
 */
class CreatePosQuoteRequest extends Request
{

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'quote_details' => 'string',
        ];
    }
}
