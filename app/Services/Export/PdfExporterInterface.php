<?php

declare(strict_types=1);

namespace App\Services\Export;

interface PdfExporterInterface extends ExporterInterface
{
    /**
     * @return resource|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function readStream();
}
