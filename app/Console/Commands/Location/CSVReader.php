<?php

namespace App\Console\Commands\Location;

use Closure;
use SplFileObject;

class CSVReader
{
    /**
     * @var Closure
     */
    private $transformer;

    /**
     * @var int
     */
    private $totalRecords;

    /**
     * @var SplFileObject
     */
    private $fileObject;

    /**
     * @param string $file
     * @param string $delimiter
     */
    public function __construct(string $file, string $delimiter = ",")
    {
        $this->fileObject = new SplFileObject($file);

        $this->fileObject->setFlags(SplFileObject::READ_CSV
            | SplFileObject::SKIP_EMPTY
            | SplFileObject::READ_AHEAD
            | SplFileObject::DROP_NEW_LINE
        );

        $this->fileObject->setCsvControl($delimiter);

        $this->fileObject->seek(PHP_INT_MAX);
        $this->totalRecords = $this->fileObject->key() + 1;
        $this->fileObject->rewind();
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public function read(Closure $callback): void
    {
        foreach ($this->fileObject as $row) {
            if ($this->transformer) {
                $row = ($this->transformer)($row);
            }
            $callback($row);
        }
    }

    /**
     * @param Closure $transformer
     * @return self
     */
    public function setTransformer(Closure $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalRecords(): int
    {
        return $this->totalRecords;
    }
}
