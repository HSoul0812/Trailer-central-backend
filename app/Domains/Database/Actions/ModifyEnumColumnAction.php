<?php

namespace App\Domains\Database\Actions;

use DB;
use Illuminate\Support\Collection;

class ModifyEnumColumnAction
{
    /** @var string */
    private $table = '';

    /** @var string */
    private $column = '';

    /** @var Collection */
    private $values;

    /** @var bool */
    private $allowNull = false;

    public function execute()
    {
        $statement = sprintf(
            "ALTER TABLE %s MODIFY COLUMN %s ENUM(%s)",
            $this->table,
            $this->column,
            $this->enumsString(),
        );

        if (!$this->allowNull) {
            $statement .= ' NOT NULL';
        }

        DB::statement($statement);
    }

    /**
     * Generate the enums string to be used in the SQL command
     *
     * @return string
     */
    private function enumsString(): string
    {
        return $this->values
            ->map(function (string $tbName) {
                return "'$tbName'";
            })
            ->implode(", ");
    }

    /**
     * @param string $table
     * @return ModifyEnumColumnAction
     */
    public function forTable(string $table): ModifyEnumColumnAction
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param string $column
     * @return ModifyEnumColumnAction
     */
    public function forColumn(string $column): ModifyEnumColumnAction
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @param Collection $values
     * @return ModifyEnumColumnAction
     */
    public function withValues(Collection $values): ModifyEnumColumnAction
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return ModifyEnumColumnAction
     */
    public function nullable(): ModifyEnumColumnAction
    {
        $this->allowNull = true;

        return $this;
    }

    /**
     * @return ModifyEnumColumnAction
     */
    public function notNullable(): ModifyEnumColumnAction
    {
        $this->allowNull = false;

        return $this;
    }
}
