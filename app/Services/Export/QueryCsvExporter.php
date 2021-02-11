<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Database\Query\Builder;
use League\Csv\Writer;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Can be used for any query to CSV export tasks
 */
abstract class QueryCsvExporter implements CsvExporterInterface
{
    use CanTrackProgress;

    /**
     * @var bool
     */
    protected $headerWritten = false;

    /**
     * @var Callable|null
     */
    protected $lineMapper;

    /**
     * @var array|null
     */
    protected $headers;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var Writer
     */
    protected $csvWriter;

    /**
     * @var Builder|EloquentBuilder
     */
    protected $query;

    /**
     * @param Builder|EloquentBuilder|null $query source query
     * @param array|null $headers array of csv headers
     * @param Callable|null $lineMapper a function that maps db columns to csv column
     * @param string $delimiter delimiter to use. change to `\t` for a tsv
     */
    public function __construct(
        ?array $headers = null,
        ?callable $lineMapper = null,
        $query = null,
        string $delimiter = self::DELIMITER_COMMA
    )
    {
        $this->query = $query;
        $this->lineMapper = $lineMapper;
        $this->headers = $headers;
        $this->delimiter = $delimiter;
    }

    /**
     * @return mixed
     */
    abstract public function createFile();

    abstract public function write($line): void;

    abstract public function deliver(): void;

    /**
     * set the query, fluent setter
     * @param Builder|EloquentBuilder $query
     * @return QueryCsvExporter
     */
    public function setQuery($query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param Callable $lineMapper
     * @return QueryCsvExporter
     */
    public function setLineMapper(callable $lineMapper): self
    {
        $this->lineMapper = $lineMapper;
        return $this;
    }

    /**
     * @param array $headers
     * @return QueryCsvExporter
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Chunked query exporter
     *
     * @return void
     */
    public function export(): void
    {
        $this->query->chunk(2000, function ($lines): void {
            foreach ($lines as $line) {
                $this->write($line);
            }
        });

        // file has been assembled, now deliver
        $this->deliver();
    }
}
