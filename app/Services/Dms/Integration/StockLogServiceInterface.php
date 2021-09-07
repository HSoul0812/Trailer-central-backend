<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

use stdClass;

interface StockLogServiceInterface
{
    /**
     * @return int number of affected rows
     *
     * @throws \PDOException when some unknown error has popped up
     */
    public function execute(string $sql): int;

    /**
     * @throws \JsonException when the metadata were unable to be serialized
     *
     * @return string SQL insert values fragment
     */
    public function mapToInsertString(stdClass $record, bool $isNotTheFirstImport): string;
}
