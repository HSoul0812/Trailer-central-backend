<?php

declare(strict_types=1);

namespace App\Services\Export;

use Barryvdh\DomPDF\PDF;
use Illuminate\Contracts\View\View;
use Throwable;

/**
 * Can be used for a simple PDF exporter in any controller
 */
class DomPdfExporterService extends PdfExporter implements DomPdfExporterServiceInterface
{
    /**
     * @var PDF
     */
    protected $engine;

    /**
     * @param View|null $view
     */
    public function __construct(?View $view = null)
    {
        parent::__construct($view);

        $this->engine = app('dompdf.wrapper');
    }

    /**
     * Print the PDF file
     *
     * @return PDF
     * @throws Throwable
     */
    public function export(): PDF
    {
        $this->engine->loadHTML($this->view->render());

        return $this->engine;
    }
}
