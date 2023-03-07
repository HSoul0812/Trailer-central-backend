<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\View\View;
use Barryvdh\Snappy\PdfWrapper;
use Throwable;

/**
 * Can be used for a simple PDF export to disk tasks
 */
class FilesystemPdfExporter extends PdfExporter implements PdfExporterInterface
{
    /** @var string a temp folder */
    public const RUNTIME_PREFIX = 'runtime/';

    public const ORIENTATION_PORTRAIT = 'portrait';
    public const ORIENTATION_LANDSCAPE = 'landscape';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Filesystem
     */
    protected $filename;

    /**
     * @var PdfWrapper
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
        $this->engine = app('snappy.pdf.wrapper');
    }

    /**
     * Print the PDF
     *
     * @return void
     * @throws Throwable
     */
    public function export(): void
    {
        $content = $this->view->render();
        ($this->afterRenderHandler)();
        $this->engine->loadHTML($content);
        $output = $this->engine->output();
        ($this->afterLoadHtmlHandler)();

        $this->filesystem->put(self::RUNTIME_PREFIX . $this->filename, $output);
    }

    /**
     * @return resource|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function readStream()
    {
        return $this->filesystem->readStream(self::RUNTIME_PREFIX . $this->filename);
    }

    public function engine(): \Barryvdh\Snappy\PdfWrapper
    {
        return $this->engine;
    }
}
