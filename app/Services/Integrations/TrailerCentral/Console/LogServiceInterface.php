<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use JsonException;
use PDOException;
use stdClass;

interface LogServiceInterface
{
    /**
     * @throws PDOException when some unknown PDO error has been thrown
     *
     * @return int number of affected rows
     */
    public function execute(string $sql): int;

    /**
     * @throws JsonException when the metadata were unable to be serialized
     *
     * @return string SQL insert values fragment
     */
    public function mapToInsertString(stdClass $record, bool $isNotTheFirstImport): string;
}
