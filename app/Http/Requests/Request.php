<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request as BaseRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;

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
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->all(), $this->getRules());

        if ($validator->fails()) {
            throw new ResourceException("Validation Failed", $validator->errors());
        }

        if ($this->validateObjectBelongsToUser()) {

            if (!$this->getObjectIdValue()) {
                throw new NoObjectIdValueSetException;
            }

            if (!$this->getObject()) {
                throw new NoObjectTypeSetException;
            }

            $dealer_id = $this->getAuthenticatedDealerId();

            if ($dealer_id && $this->getObjectIdValue()) {
                $obj = $this->getObject()->findOrFail($this->getObjectIdValue());

                // false in case the object does not belongs to the dealer who has made the request
                return $dealer_id === $obj->dealer_id;
            }
        }

        return true;
    }

    /**
     * This function is to be able to test any request using the guard `validateObjectBelongsToUser` cuz `Auth::user()` is not testable
     *
     * @return int
     */
    private function getAuthenticatedDealerId(): int
    {
        // looking for a client provided parameters seems fool, but remember that `setDealerIdOnRequest` middleware is in charge of this
        $dealer_id = $this->input('dealer_id');

        return $dealer_id ?? Auth::user()->dealer_id;
    }

    /**
     * @return mixed
     */
    protected function getObject() {
        return false;
    }

    protected function getObjectIdValue()
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
