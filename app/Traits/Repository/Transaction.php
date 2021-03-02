<?php

namespace App\Traits\Repository;

use Illuminate\Support\Facades\DB;

/**
 * Trait Transaction
 * @package App\Traits\Repository
 */
trait Transaction
{
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commitTransaction(): void
    {
        DB::commit();
    }

    public function rollbackTransaction(): void
    {
        DB::rollback();
    }
}
