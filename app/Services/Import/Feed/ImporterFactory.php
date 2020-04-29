<?php


namespace App\Services\Import\Feed;


use App\Services\Import\Feed\Type\Norstar;

/**
 * Class ImporterFactory
 *
 * Create instances of dealer specific importers
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
        switch ($code) {
            case 'norstar':
                return app(Norstar::class);

            default:
                throw new \Exception("Unknown importer type: {$code}");
        }
    }
}
