<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console\Leads;

use App\Models\Leads\LeadLog;
use App\Services\Integrations\TrailerCentral\Console\AbstractLogService;
use JsonException;
use PDOException;
use stdClass;

class LogService extends AbstractLogService implements LogServiceInterface
{
    /**
     * @throws PDOException when some unknown PDO error has been thrown
     */
    public function execute(string $sql): int
    {
        return $this->pdo->exec($this->compileInsertStatement($sql));
    }

    /**
     * @throws JsonException when some column was unable to be serialized
     *
     * @return string SQL insert values fragment
     */
    public function mapToInsertString(stdClass $record, bool $isNotTheFirstImport): string
    {
        return sprintf(
            '(%d, %s, %s, %s, %s, %s, %s, %s, %s),',
            $record->inventory_id,
            $record->first_name ? $this->quote($record->first_name) : 'NULL',
            $record->last_name ? $this->quote($record->last_name) : 'NULL',
            $record->email_address ? $this->quote($record->email_address) : 'NULL',
            $record->date_submitted ? $this->quote($record->date_submitted) : 'NULL',
            $this->quote(str_replace('\\u0000', ' ', json_encode((array) $record, JSON_THROW_ON_ERROR))),
            $record->vin ? $this->quote($record->vin) : 'NULL',
            $record->manufacturer ? $this->quote($record->manufacturer) : 'NULL',
            $record->brand ? $this->quote($record->brand) : 'NULL'
        );
    }

    private function compileInsertStatement(string $values): string
    {
        return sprintf(
            'INSERT INTO %s (
                        trailercentral_id,
                        first_name,
                        last_name,
                        email_address,
                        submitted_at,
                        "meta",
                        vin,
                        manufacturer,
                        brand) VALUES %s ',
            LeadLog::getTableName(),
            substr($values, 0, -1)
        );
    }
}
