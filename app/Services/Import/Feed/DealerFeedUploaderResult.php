<?php


namespace App\Services\Import\Feed;


class DealerFeedUploaderResult
{
    const RESULT_SUCCESS = 'success';
    const RESULT_ERROR = 'error';

    /**
     * @var string success or fail
     */
    private $result;

    /**
     * @var int records processed
     */
    private $records;

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult(string $result): void
    {
        $this->result = $result;
    }

    /**
     * @return int
     */
    public function getRecords(): int
    {
        return $this->records;
    }

    /**
     * @param int $records
     */
    public function setRecords(int $records): void
    {
        $this->records = $records;
    }

}
