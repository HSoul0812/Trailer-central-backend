<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\View\View;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Can be used for a simple PDF export to disk tasks
 */
class FilesystemPdfExporter extends PdfExporter implements ExporterInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Filesystem
     */
    protected $filename;

    /**
     * @var PDF
     */
    protected $engine;

    /**
     * @param Filesystem $filesystem
     * @param string $filename
     * @param View|null $view
     */
    public function __construct(Filesystem $filesystem, string $filename, ?View $view = null)
    {
        parent::__construct($view);

        $this->filesystem = $filesystem;
        $this->filename = $filename;
        $this->engine = app('dompdf.wrapper');
    }

    /**
     * Print the PDF
     *
     * @return void
     * @throws Throwable
     */
    public function export(): void
    {
        $this->engine->loadHTML($this->view->render());
        Storage::disk('tmp')->put($this->filename, $this->engine->output());
    }
}
