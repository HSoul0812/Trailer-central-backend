<?php

namespace App\Exceptions\Nova\Actions\Dealer;

/**
 * EcommerceActivationException
 *
 * Use this instead of \Exception to throw any kind of full ecommerce activation exception
 *
 */
class EcommerceDeactivationException extends \Exception
{

    protected $message = 'E-Commerce deactivation error';

}