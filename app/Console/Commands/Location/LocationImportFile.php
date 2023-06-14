<?php

namespace App\Console\Commands\Location;

class LocationImportFile
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var int
     */
    private $zipcodeColumn;

    /**
     * @var int
     */
    private $longitudeColumn;

    /**
     * @var int
     */
    private $latitudeColumn;

    /**
     * @var int
     */
    private $cityColumn;

    /**
     * @var int
     */
    private $stateColumn;

    /**
     * @var int
     */
    private $countryColumn;

    /**
     * @param string $file
     * @param string $delimiter
     * @param int $zipcodeColumn
     * @param int $cityColumn
     * @param int $stateColumn
     * @param int $countryColumn
     * @param int $longitudeColumn
     * @param int $latitudeColumn
     */
    public function __construct(
        string $file,
        string $delimiter,
        int    $zipcodeColumn,
        int    $cityColumn,
        int    $stateColumn,
        int    $countryColumn,
        int    $longitudeColumn,
        int    $latitudeColumn
    )
    {
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->zipcodeColumn = $zipcodeColumn;
        $this->longitudeColumn = $longitudeColumn;
        $this->latitudeColumn = $latitudeColumn;
        $this->cityColumn = $cityColumn;
        $this->stateColumn = $stateColumn;
        $this->countryColumn = $countryColumn;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return int
     */
    public function getZipcodeColumn(): int
    {
        return $this->zipcodeColumn;
    }

    /**
     * @return int
     */
    public function getLongitudeColumn(): int
    {
        return $this->longitudeColumn;
    }

    /**
     * @return int
     */
    public function getLatitudeColumn(): int
    {
        return $this->latitudeColumn;
    }

    /**
     * @return int
     */
    public function getCityColumn(): int
    {
        return $this->cityColumn;
    }

    /**
     * @return int
     */
    public function getStateColumn(): int
    {
        return $this->stateColumn;
    }

    /**
     * @return int
     */
    public function getCountryColumn(): int
    {
        return $this->countryColumn;
    }
}
