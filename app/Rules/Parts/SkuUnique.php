<?php

namespace App\Rules\Parts;

use Illuminate\Contracts\Validation\Rule;
use App\Repositories\Parts\PartRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class SkuUnique implements Rule
{

    public function validate($attribute, $value, $parameters) {            
        $partRepository = app(PartRepositoryInterface::class);
        
        if (!empty($parameters)) {
            $partId = current($parameters);            
            $part = $partRepository->get(['id' => $partId]);
            if ($part->sku === $value) {
                return true;
            }
        }
        
        if (empty(Auth::user())) {
            $part = $partRepository->getBySku($value);
        } else {
            $part = $partRepository->getDealerSku(Auth::user()->dealer_id, $value);
        }
        
        
        if ($part) {
            return false;
        }
        
        return true;        
    }
    
    public function passes($attribute, $value) {
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is not unique in the DB.';
    }

}