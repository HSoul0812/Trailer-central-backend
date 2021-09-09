<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration;

use App\Models\InventoryLog;
use App\Repositories\InventoryLogRepositoryInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use PDO;
use stdClass;

class InventoryLogService implements InventoryLogServiceInterface
{
    private Closure|PDO $pdo;

    public function __construct(private InventoryLogRepositoryInterface $repository)
    {
        $this->pdo = DB::connection()->getPdo();
    }

    /**
     * @throws \PDOException when some unknown PDO error has been thrown
     */
    public function execute(string $sql): int
    {
        return $this->pdo->exec($this->compileInsertStatement($sql));
    }

    /**
     * @return string SQL insert values fragment
     *
     * @throws \JsonException when the metadata were unable to be serialized
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
            'INSERT INTO %s (record_id, "event", status, vin, brand, manufacturer, price, "meta") VALUES %s ',
            InventoryLog::getTableName(),
            substr($values, 0, -1)
        );
    }

    private function quote(mixed $value): string
    {
        return $this->pdo->quote($value);
    }

    /**
     * Maps a status as integer (DMS type) to a string.
     */
    private function mapStatus(?int $status): string
    {
        return match ($status) {
            2, 3, 4, 5, 6 => InventoryLog::STATUS_SOLD,
            default => InventoryLog::STATUS_AVAILABLE
        };
    }
}
