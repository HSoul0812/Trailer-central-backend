<?php


namespace App\Http\Requests\MapService;


use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class GeocodeRequest extends Request implements IndexRequestInterface
{
    protected array $rules = [
        'q' => 'required'
    ];

    public function validate(): bool {
        return parent::validate();
    }
}
