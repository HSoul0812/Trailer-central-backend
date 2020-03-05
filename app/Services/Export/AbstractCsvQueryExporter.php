<?php


namespace App\Services\Export;


use Illuminate\Database\Query\Builder;
use League\Csv\Writer;

abstract class AbstractCsvQueryExporter extends AbstractQueryExporter
{
    protected $headerWritten = false;
    private $lineMapper;
    private $headers;
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

        // make a temp file use a league csv writer; fileHandle is called previously
        // TODO see if a temp file can be skipped and data can be streamed directly to Storage::put()
        $this->csvWriter = Writer::createFromStream($this->tmpFileHandle);
        $this->csvWriter->setDelimiter($delimiter);
    }

    /**
     * Assembles the line to write
     *
     * @param array $line a line of data to write
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function write($line)
    {
        // for the first write add a header
        if (!$this->headerWritten) {
            $this->csvWriter->insertOne($this->headers);
            $this->headerWritten = true;
        }

        // insert to csv using the supplied line mapper
        $this->csvWriter->insertOne(call_user_func([$this, 'lineMapper'], $line));
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
