<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request as BaseRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\RequestInterface;

class Request extends BaseRequest implements RequestInterface {

    /**
     * Rules to validate
     *
     * @var array
     */
    protected $rules = [];

    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->all(), $this->getRules());

        if ($validator->fails()) {
            throw new ResourceException("Validation Failed", $validator->errors());
        }

        return true;
    }

    protected function getRules(): array
    {
        return $this->rules;
    }
}
