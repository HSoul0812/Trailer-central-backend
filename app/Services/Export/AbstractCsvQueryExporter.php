<?php


namespace App\Services\Export;


use Illuminate\Database\Query\Builder;
use League\Csv\Writer;

/**
 * Class AbstractCsvQueryExporter
 *
 * Can be used for any query to CSV export tasks
 *
 * @package App\Services\Export
 */
abstract class AbstractCsvQueryExporter extends AbstractQueryExporter
{
    protected $headerWritten = false;
    protected $lineMapper;
    protected $headers;
    protected $delimiter;

    /**
     * @var Writer
     */
    protected $csvWriter;

    /**
     * AbstractCsvExporter constructor.
     * @param Builder $query source query
     * @param array $headers array of csv headers
     * @param Callable $lineMapper a function that maps db columns to csv column
     * @param string $delimiter delimiter to use. change to `\t` for a tsv
     * @throws \League\Csv\Exception
     */
    public function __construct($query=null, $headers=null, $lineMapper=null, $delimiter = ',')
    {
        parent::__construct($query);
        $this->lineMapper = $lineMapper;
        $this->headers = $headers;
        $this->delimiter = $delimiter;
    }


    /**
     * @param Callable $lineMapper
     * @return AbstractCsvQueryExporter
     */
    public function setLineMapper(Callable $lineMapper)
    {
        $this->lineMapper = $lineMapper;
        return $this;
    }

    /**
     * @param array $headers
     * @return AbstractCsvQueryExporter
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }
}
