<?php

declare(strict_types=1);

namespace App\Exceptions\Dms\Integration;

use JetBrains\PhpStorm\Pure;
use Throwable;

class SyncProcessIsStillWorking extends \LogicException
{
    #[Pure]
    public function __construct($message = 'another sync process is still working', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
