<?php

namespace App\Domains\ViewedDealer\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DealerIdExistsException extends Exception
{
    public static function make(int $dealerId): DealerIdExistsException
    {
        $message = sprintf(
            'Dealer ID %d already exists in the database, operation aborted.',
            $dealerId,
        );

        return new DealerIdExistsException(
            message: $message,
            code: Response::HTTP_BAD_REQUEST,
        );
    }
}
