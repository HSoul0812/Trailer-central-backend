<?php

namespace App\Exceptions\Nova\Actions\Dealer;

/**
 * EcommerceActivationException
 *
 * Use this instead of \Exception to throw any kind of full ecommerce activation exception
 *
 */
class EcommerceActivationException extends \Exception
{

    protected $message = 'E-Commerce activation error';

}