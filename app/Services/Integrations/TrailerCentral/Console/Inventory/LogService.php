<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console\Inventory;

use App\Models\Inventory\InventoryLog;
use App\Repositories\Inventory\InventoryLogRepositoryInterface;
use App\Services\Integrations\TrailerCentral\Console\AbstractLogService;
use Illuminate\Database\ConnectionInterface;
use JsonException;
use PDOException;
use stdClass;

class LogService extends AbstractLogService implements LogServiceInterface
{
    public function __construct(protected InventoryLogRepositoryInterface $repository, ConnectionInterface $connection)
    {
        parent::__construct($connection);
    }

    /**
     * @throws PDOException when some unknown PDO error has been thrown
     */
    public function execute(string $sql): int
    {
        return $this->pdo->exec($this->compileInsertStatement($sql));
    }

    /**
     * @throws JsonException when the metadata were unable to be serialized
     *
     * @return string SQL insert values fragment
     */
    public function mapToInsertString(stdClass $record, bool $isNotTheFirstImport): string
    {
        $log = $this->getPreviousDataState($isNotTheFirstImport, $record->inventory_id);

        if ($log) {
            $eventName = $log->price == $record->price ? InventoryLog::EVENT_UPDATED : InventoryLog::EVENT_PRICE_CHANGED;

            return sprintf(
                '(%d, %s, %s, %s, %s, %s, %f, %s),',
                $record->inventory_id,
                $this->quote($eventName),
                $this->quote($this->mapStatus($record->status)),
                $record->vin ? $this->quote($record->vin) : 'NULL',
                $record->brand ? $this->quote($record->brand) : 'NULL',
                $this->quote($record->manufacturer ?: 'na'),
                (float) $record->price,
                $this->quote(json_encode((array) $record, JSON_THROW_ON_ERROR))
            );
        }

        return sprintf(
            '(%d, %s, %s, %s, %s, %s, %f, %s),',
            $record->inventory_id,
            $this->quote('created'),
            $this->quote($this->mapStatus($record->status)),
            $record->vin ? $this->quote($record->vin) : 'NULL',
            $record->brand ? $this->quote($record->brand) : 'NULL',
            $this->quote($record->manufacturer ?: 'na'),
            (float) $record->price,
            $this->quote(json_encode((array) $record, JSON_THROW_ON_ERROR))
        );
    }

    private function getPreviousDataState(bool $isNotTheFirstImport, int $recordId): ?InventoryLog
    {
        return $isNotTheFirstImport ? $this->repository->lastByRecordId($recordId) : null;
    }

    private function compileInsertStatement(string $values): string
    {
        return sprintf(
            'INSERT INTO %s (trailercentral_id, "event", status, vin, brand, manufacturer, price, "meta") VALUES %s ',
            InventoryLog::getTableName(),
            substr($values, 0, -1)
        );
    }

    /**
     * Maps a status as integer (Trailer Central type) to a string used by Trailer Trader.
     */
    private function mapStatus(?int $status): string
    {
        return match ($status) {
            2, 3, 4, 5, 6 => InventoryLog::STATUS_SOLD,
            default => InventoryLog::STATUS_AVAILABLE
        };
    }
}
