<?php

namespace App\Domains\Compression\Actions;

use App\Domains\Compression\Exceptions\GzipFailedException;

class CompressFileWithGzipAction
{
    /**
     * @throws GzipFailedException
     */
    public function execute(string $filePath): string
    {
        $zipFilePath = "$filePath.gz";

        $output = 'N/A';

        // We'll zip using the compression level 9 for best compression rate
        exec("gzip -9 -c $filePath > $zipFilePath", $output, $resultCode);

        if ($resultCode !== 0) {
            throw new GzipFailedException("Failed to gzip $filePath, output: $output");
        }

        return $zipFilePath;
    }
}
