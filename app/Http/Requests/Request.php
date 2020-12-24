<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request as BaseRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 *
 * @author Eczek
 */
class Request extends BaseRequest {

    /**
     * Rules to validate
     *
     * @var array
     */
    protected $rules = [];

    /**
     * @return bool it is true when the object belong to the current logged in dealer
     *
     * @throws ResourceException when there were some validation error
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->all(), $this->getRules());

        if ($validator->fails()) {
            throw new ResourceException("Validation Failed", $validator->errors());
        }

        if ($this->validateObjectBelongsToUser()) {
            $user = Auth::user();

            if ($user) {
                if ($this->getObjectIdValue()) {
                    $obj = $this->getObject()->findOrFail($this->getObjectIdValue());
                    if ($user->dealer_id != $obj->dealer_id) {
                        return false;
                    }
                }

            }
        }

        return true;
    }

    protected function getObjectIdValue(): bool
    {
        return false;
    }

    protected function validateObjectBelongsToUser(): bool
    {
        return false;
    }

    protected function getRules(): array
    {
        return $this->rules;
    }
}
