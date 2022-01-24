<?php

namespace App\Exceptions;

/**
 * @author Eczek
 */
class NotImplementedException extends \BadMethodCallException {
    protected $code = 501;
}
