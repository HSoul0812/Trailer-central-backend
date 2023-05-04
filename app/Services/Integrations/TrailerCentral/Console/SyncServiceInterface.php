<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use Exception;
use JsonException;
use PDOException;

interface SyncServiceInterface
{
    /**
     * @throws PDOException  when some unknown PDO error has been thrown
     * @throws JsonException when the metadata were unable to be serialized
     * @throws Exception     when some unknown exception has been thrown
     */
    public function sync(): int;
}
