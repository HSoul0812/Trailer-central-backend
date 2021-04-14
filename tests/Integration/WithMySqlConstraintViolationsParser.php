<?php

declare(strict_types=1);

namespace Tests\Integration;

use ErrorException;
use Illuminate\Support\Facades\DB;
use Tests\Exceptions\ExpectationException;

/**
 * @see https://dev.mysql.com/doc/mysql-errors/5.6/en/server-error-reference.html
 */
trait WithMySqlConstraintViolationsParser
{
    /**
     * Gets exception message for the integrity constrain violation 1452.
     *
     * @param string $tableName      the table name that has defined the constraint name
     * @param string $constraintName the constraint name defined in table schema
     *
     * @throws ExpectationException when there is not a constraint definition for a constraint name
     *
     * @return string the exception message well-formed for the passed constraint name
     */
    protected function getCannotInsertOrUpdateMessage(string $tableName, string $constraintName): string
    {
        $failedExpectationMessage = "There is not constraint for $constraintName on table $tableName";
        $schemaName = DB::connection()->getDatabaseName();

        try {
            $constraintQuotedName = "`$constraintName`";

            // Gets table schema
            $tableSchema = ((array) DB::select("SHOW CREATE TABLE $tableName")[0])['Create Table'];

            $pattern = "/(?P<constraint>CONSTRAINT {$constraintQuotedName}.*[^,\r\n]+)/m";
            // Gets the content of the first line with the pattern "CONSTRAINT `constraint_name`"
            if (!preg_match($pattern, $tableSchema, $matches)) {
                throw new ExpectationException($failedExpectationMessage);
            }

            return 'SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: ' .
                "a foreign key constraint fails (`$schemaName`.`$tableName`, CONSTRAINT $constraintQuotedName";
        } catch (ErrorException $exception) {
            throw new ExpectationException($failedExpectationMessage);
        }
    }

    /**
     * Gets exception message for the integrity constrain violation 1062.
     *
     * @param  string  $entry a string delimited by dash
     *                        i.e 1452-54878
     *                        i.e 14788-Batmobil
     * @param  string  $constraintName  the constraint name defined in table schema
     *
     * @return string the exception message well-formed for the passed constraint name
     */
    protected function getDuplicateEntryMessage(string $entry, string $constraintName): string
    {
        return sprintf(
            "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '%s' for key '%s",
            $entry,
            $constraintName);
    }
}
