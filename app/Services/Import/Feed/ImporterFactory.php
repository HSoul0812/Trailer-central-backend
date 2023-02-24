<?php


namespace App\Services\Import\Feed;

use Illuminate\Support\Facades\Log;

/**
 * Class ImporterFactory
 *
 * Create instances of factory uploader
 *
 * @package App\Services\Import\Feed
 */
class ImporterFactory
{
    /**
     * @param $code
     * @return mixed
     * @throws \Exception
     */
    public function build($code)
    {
        Log::info('Building for ' . $code);
        return app(FactoryUpload::class);
    }
}
