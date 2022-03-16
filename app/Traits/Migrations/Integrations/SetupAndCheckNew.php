<?php

namespace App\Traits\Migrations\Integrations;

use App\Models\Integration\Integration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class SetupAndCheckNew
 * @package App\Traits\Migrations\Integrations
 */
trait SetupAndCheckNew
{
    /**
     * @var string
     */
    private static $tableName = 'integration';

    /**
     * @param string $integrationName
     * @return int
     */
    public function getNextId(string $integrationName): int
    {
        $check = Integration::where('code', Str::lower($integrationName))->orWhere('module_name', $integrationName)->first();
        $exists = 0;
        if ($check === null)
        {
            $exists = $this->getNextIdFromDb();
        }

        return $exists;
    }

    /**
     * @return int
     */
    public function getNextIdFromDb(): int
    {
        return DB::table(self::$tableName)->max('integration_id') + 1;
    }

}
