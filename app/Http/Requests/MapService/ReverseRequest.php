<?php

declare(strict_types=1);

namespace App\Http\Requests\MapService;

use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class ReverseRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'lat' => 'required|between:-90,90',
        'lng' => 'required|between:-180,180',
    ];

    public function validate(): bool
    {
        return parent::validate();
    }
}
