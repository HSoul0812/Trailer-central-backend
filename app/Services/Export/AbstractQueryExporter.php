<?php


namespace App\Services\Export;


use Illuminate\Database\Query\Builder;

/**
 * Class AbstractExporter
 *
 * base class for data exporters
 *
 * @package App\Services\Export
 */
abstract class AbstractQueryExporter implements ExporterInterface
{
    use CanTrackProgress;

    public function __construct($query = null)
    {
        $this->query = $query;
        $this->createFile();
    }

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var resource
     */
    protected $tmpFileHandle = null;

    abstract function createFile();

    abstract function write($line);

    abstract function deliver();

    /**
     * set the query, fluent setter
     * @param Builder $query
     * @return AbstractQueryExporter
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Chunked query exporter
     *
     * @return void
     */
    public function export()
    {
        $this->query->chunk(2000, function ($lines)  {
            foreach ($lines as $line) {
                $this->write($line);
            }
        });

        // file has been assembled, now deliver
        $this->deliver();
    }
}
