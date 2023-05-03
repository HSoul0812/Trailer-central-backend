<?php

namespace App\Domains\ViewedDealer\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DuplicateDealerIdException extends Exception
{
    /**
     * Convenient method to create this exception instance.
     */
    public static function make(string $name1, string $name2, int $dealerId): DuplicateDealerIdException
    {
        $message = sprintf(
            "Dealer name '%s' and '%s' has the same dealer id of %d, operation aborted.",
            $name1,
            $name2,
            $dealerId,
        );

        return new DuplicateDealerIdException(
            message: $message,
            code: Response::HTTP_BAD_REQUEST,
        );
    }
}
