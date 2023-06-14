<?php

namespace App\Console\Commands\Location;

use App\Models\User\Location\Geolocation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class LatLongPrecisionUpdaterCommand extends Command
{
    private const IMPORT_FILE_MIMES = [
        'text/plain',
        'text/csv'
    ];

    private const TAB_DELIMITER = "\t";

    private const COMMA_DELIMITER = ",";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolocation:precision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the precision of the records in the geolocation table';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        try {
            $importFile = $this->getImportFileInfo();

            if ($this->confirm(
                sprintf('Are you sure? This would update the geolocation table in %s with records from %s',
                    env('DB_HOST'),
                    $importFile->getFile()
                )
            )) {
                DB::transaction(function () use ($importFile) {
                    $reader = new CSVReader($importFile->getFile(), $importFile->getDelimiter());

                    $progress = $this->output->createProgressBar($reader->getTotalRecords());

                    $reader->setTransformer(function ($row) use ($importFile) {
                        if (count($row) == 1 && str_contains($row[0], $importFile->getDelimiter())) {
                            $row = explode($importFile->getDelimiter(), $row[0]);
                        }

                        $country = $row[$importFile->getCountryColumn()];
                        $state = $row[$importFile->getStateColumn()];
                        $city = strtoupper($row[$importFile->getCityColumn()]);
                        if (empty($state)) {
                            $cityWithState = explode(' ', $city);
                            throw_unless(
                                count($cityWithState) == 2,
                                new Exception('Could not get state from city: ' . $city)
                            );
                            $city = $cityWithState[0];
                            $state = $cityWithState[1];
                        }
                        return [
                            'zip' => $row[$importFile->getZipcodeColumn()],
                            'latitude' => $row[$importFile->getLatitudeColumn()],
                            'longitude' => $row[$importFile->getLongitudeColumn()],
                            'city' => $city,
                            'state' => $state,
                            'country' => $country == 'US' ? 'USA' : $country
                        ];
                    })->read(function (array $row) use (&$progress) {
                        Geolocation::updateOrCreate([
                            'zip' => $row['zip'],
                            'country' => $row['country']
                        ], $row);

                        $progress->advance();
                    });

                    $progress->finish();

                    $this->getOutput()->newLine();

                    $this->info('Geolocation table updated successfully.');
                });
            }

            return 0;
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
    }

    /**
     * @throws Throwable
     */
    protected function getImportFileInfo(): LocationImportFile
    {
        $locationFile = $this->ask('Absolute path to the csv file which contains the locations:');
        $this->validateImportFile($locationFile);

        $row = $this->getFirstRowOfCSV($locationFile);
        $data = $row['data'];

        $countriesColumn = $this->ask('Which column contains the countries?');
        $this->validateColumnExistence($data, $countriesColumn);
        $zipCodeColumn = $this->ask('Which column contains the zipcodes?');
        $this->validateColumnExistence($data, $zipCodeColumn);
        $citiesColumn = $this->ask('Which column contains the cities?');
        $this->validateColumnExistence($data, $citiesColumn);
        $statesColumn = $this->ask('Which column contains the states?');
        $this->validateColumnExistence($data, $statesColumn);
        $latitudeColumn = $this->ask('Which column contains the latitudes?');
        $this->validateColumnExistence($data, $latitudeColumn);
        $longitudeColumn = $this->ask('Which column contains the longitudes?');
        $this->validateColumnExistence($data, $longitudeColumn);

        return new LocationImportFile(
            $locationFile,
            $row['delimiter'],
            $zipCodeColumn,
            $citiesColumn,
            $statesColumn,
            $countriesColumn,
            $longitudeColumn,
            $latitudeColumn
        );
    }

    /**
     * @throws Throwable
     */
    protected function validateImportFile(string $file)
    {
        throw_unless(file_exists($file), new Exception(sprintf('%s does not exist', $file)));
        throw_unless(in_array(mime_content_type($file), self::IMPORT_FILE_MIMES), new Exception());
    }

    /**
     * @throws Throwable
     */
    protected function getFirstRowOfCSV(string $file): array
    {
        $handle = fopen($file, 'r');
        throw_unless($line = fgetcsv($handle), new Exception(sprintf('Unable to read %s as CSV', $file)));
        fclose($handle);
        $data = current($line);
        $delimiter = $this->detectDelimiter($data);
        return ['delimiter' => $delimiter, 'data' => explode($delimiter, $data)];
    }

    /**
     * @param string $line
     * @return string
     */
    protected function detectDelimiter(string $line): string
    {
        $commaCount = substr_count($line, self::COMMA_DELIMITER);
        $tabCount = substr_count($line, self::TAB_DELIMITER);
        return $tabCount > $commaCount ? self::TAB_DELIMITER : self::COMMA_DELIMITER;
    }

    /**
     * @throws Throwable
     */
    protected function validateColumnExistence(array $column, string $columnIndex)
    {
        throw_unless(isset($column[$columnIndex]), new Exception(sprintf('Column %s does not exist', $columnIndex)));
    }
}
