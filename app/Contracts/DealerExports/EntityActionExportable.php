<?php

namespace App\Contracts\DealerExports;

use Illuminate\Database\Query\Builder;

interface EntityActionExportable {
    public function getQuery();

    public function transformRow($row);
}
