<?php

declare(strict_types=1);

namespace App\Services\Export;

use Barryvdh\DomPDF\PDF;

interface DomPdfExporterServiceInterface extends ExporterInterface
{
    /**
     * @return PDF
     */
    public function export(): PDF;
}
