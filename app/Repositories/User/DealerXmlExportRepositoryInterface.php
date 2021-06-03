<?php

namespace App\Repositories\User;

use App\Repositories\Repository;
use App\Models\User\DealerXmlExport;

interface DealerXmlExportRepositoryInterface extends Repository 
{
    public function updateExport(int $dealerId, bool $exportStatus) : DealerXmlExport;
}
