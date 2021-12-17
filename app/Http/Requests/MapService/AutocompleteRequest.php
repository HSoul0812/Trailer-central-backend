<?php

declare(strict_types=1);

namespace App\Http\Requests\MapService;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class AutocompleteRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'q' => 'required',
    ];

    public function validate(): bool
    {
        return parent::validate();
    }
}
