<?php

namespace App\Console\Commands\Website;

use App\Repositories\Website\TowingCapacity\MakesRepositoryInterface;
use App\Repositories\Website\TowingCapacity\VehiclesRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class ScrapeTrailerTowingGuide
 * @package App\Console\Commands\Website
 */
class ScrapeTrailerTowingGuide extends Command
{
    private const TOW_LIMIT_PATTERN = '/(.*)?(\s+[0-9][0-9]?,\d{3}\+?(\s+|\\\|\/)?((\*|\s+)?\(?(y|w|v|I|u|x|o|m|n|s|z|r|q|b|c|g|e|p|f|l|j|d|h|t|i|k|NA|All|(a\d{0,2})|(m\d{0,2}))\*{0,2}\)?(\-|,|\/|(\s?or\s?)|(\s?and\s?))?\*{0,2}){0,3}\)?([0-9][0-9]?,\d{3})?\*{0,2}((\*|\s+)?\(?(y|w|v|I|u|x|o|m|n|s|z|r|q|b|c|g|e|p|f|l|j|d|h|t|i|k|NA|All|(a\d{0,2})|(m\d{0,2}))\*{0,2}(\-|,|\/|(\s?or\s?)|(\s?and\s?))?\*{0,2}){0,4}\)?)$/';
    private const ENGINE_PATTERN = '/(.*)?\s+((DAE)|(Electric)|(All except Hybrid)|(\(?all\)?)|(\(?All\)?)|([1-8]\.[0-9](L|l)(\s+HEV)?(\s+((V-[6,8])|(I-[4,6])|(H-[4,6])))?(\s+(TC|TD|HO)\/?){0,2}))\s+/';

    private const BROKE_SYMBOLS_PATTERN = '/\)\s*((\*|★|●|▲|#|◆|■|▼|\d)\s*){1,5}$/';
    private const BROKE_SYMBOLS_PATTERN2 = '/\s*((\*|★|●|▲|#|◆|■|▼)\s*){1,5}$/';

    private const DRIVE_TRAIN_PATTERN = '/\/?(AWD)|(FWD)|(RWD)|(2WD)|(4WD)\/?/';
    private const DRIVE_TRAIN_PATTERN_ALL = '/(\/?((AWD)|(FWD)|(RWD)|(2WD)|(4WD))\/?)/';

    private const VEHICLE_OPTIONS_MAPPING = [
        '/(\S+\s+)\/?Reg Cab\/?(\s+|$)/' => '$1Regular Cab$2',
        '/(\S+\s+)\/?Ext Cab\/?(\s+|$)/' => '$1Extended Cab$2',
        '/(\S+\s+)\/?Ext\/?(\s+|$)/' => '$1Extended Cab$2',
        '/(\S+\s+)\/?CC\/?(\s+|$)/' => '$1Crew Cab$2',
        '/(\S+\s+)\/?DC\/?(\s+|$)/' => '$1Double Cab$2',
        '/(\S+\s+)\/?SB\/?(\s+|$)/' => '$1Shortbed$2',
        '/(\S+\s+)\/?Std Bed\/?(\s+|$)/' => '$1Standard Bed$2',
        '/(\S+\s+)\/?LB\/?(\s+|$)/' => '$1Longbed$2',
        '/(\S+\s+)\/?CV\/?(\s+|$)/' => '$1Cargo Van$2',
        '/(\S+\s+)\/?CrV\/?(\s+|$)/' => '$1Crew Van$2',
        '/(\S+\s+)\/?PV\/?(\s+|$)/' => '$1Passenger Van$2',
        '/(\S+\s+)\/?SRW\/?(\s+|$)/' => '$1Single Rear Wheel$2',
        '/(\S+\s+)\/?DRW\/?(\s+|$)/' => '$1Dual Rear Wheel$2',
        '/(\S+\s+)\(All\)(\s+|$)/' => '$1$2',
        '/(\S+\s+)\(all\)(\s+|$)/' => '$1$2',
        '/(\S+\s+)\/?SWB\/?(\s+|$)/' => '$1Short Wheelbase$2',
        '/(\S+\s+)\/?MWB\/?(\s+|$)/' => '$1Medium Wheelbase$2',
        '/(\S+\s+)\/?LWB\/?(\s+|$)/' => '$1Long Wheelbase$2',
        '/(\S+\s+)\/?EL\/?(\s+|$)/' => '$1Extra Length$2',
        '/(\S+\s+)\/?LR\/?(\s+|$)/' => '$1Low Roof$2',
        '/(\S+\s+)\/?MR\/?(\s+|$)/' => '$1Medium Roof$2',
        '/(\S+\s+)\/?HR\/?(\s+|$)/' => '$1High Roof$2',
        '/(\S+\s+)\/?EHR\/?(\s+|$)/' => '$1Extra-High Roof$2',
        '/(\S+\s+)\/?QC\/?(\s+|$)/' => '$1Quad Cab$2',
        '/(\S+\s+)\/?RWB\/?(\s+|$)/' => '$1Regular Wheelbase$2',
        '/(\S+\s+)\/?KC\/?(\s+|$)/' => '$1King Cab$2',
        '/(\S+\s+)\/?SC\/?(\s+|$)/' => '$1Supercharged$2',
    ];

    private const DRIVE_TRAIN_REPLACE = [
        'AWD' => 'All-Wheel Drive',
        'FWD' => 'Front-Wheel Drive',
        'RWD' => 'Rear-Wheel Drive',
        '2WD' => 'Two-Wheel Drive',
        '4WD' => 'Four-Wheel Drive',
    ];

    /**
     * @var array
     */
    private $makes = [];

    /**
     * @var MakesRepositoryInterface
     */
    private $makesRepository;

    /**
     * @var VehiclesRepositoryInterface
     */
    private $vehiclesRepository;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:scrape_trailer_towing_guide";

    /**
     * ScrapeTrailerTowingGuide constructor.
     * @param MakesRepositoryInterface $makesRepository
     * @param VehiclesRepositoryInterface $vehiclesRepository
     */
    public function __construct(MakesRepositoryInterface $makesRepository, VehiclesRepositoryInterface $vehiclesRepository)
    {
        parent::__construct();

        $this->makesRepository = $makesRepository;
        $this->vehiclesRepository = $vehiclesRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->makesRepository->deleteAll();
        $this->vehiclesRepository->deleteAll();

        $files = File::allFiles(env('APP_TRAILER_TOWING_FILES_DIR'));

        $vehicles = [];

        foreach ($files as $file) {
            $year = $file->getBasename('.' . $file->getExtension());

            $items = $this->buildItems($file->getContents());

            foreach ($items as &$item) {
                $item['year'] = $year;
            }

            $vehicles = array_merge($vehicles, $items);
        }

        $this->addMakes();

        $makesNameId = [];

        foreach ($this->makesRepository->getAll([]) as $make) {
            $makesNameId[$make['name']] = $make['id'];
        }

        foreach ($vehicles as &$vehicle) {
            $vehicle['make_id'] = $makesNameId[$vehicle['make']];
            unset($vehicle['make']);
        }

        foreach (array_chunk($vehicles,1000) as $chunkVehicles) {
            $this->addVehicles($chunkVehicles);
        }

        return true;
    }

    /**
     * @param string $content
     * @return array
     */
    private function buildItems(string $content): array
    {
        $tmpItems = [];
        $items = [];

        foreach (preg_split("/(\n){3}/", $content) as $tmpItem2) {
            $tmpItem2 = $this->sanitizeItem($tmpItem2);
            $make = strtoupper(trim(strstr($tmpItem2, PHP_EOL, true)));
            $tmpItems[$make] = trim(substr_replace($tmpItem2, '', strpos($tmpItem2, $make), strlen($make)));

            if (!in_array($make, $this->makes)) {
                $this->makes[] = $make;
            }
        }

        foreach ($tmpItems as $make => $tmpItem) {
            foreach (preg_split("/\n{2}/", $tmpItem) as $tmpItem2) {
                $tmpItems3 = preg_split("/\n/", $tmpItem2);
                $defaultModel = null;

                $tmpItems3 = array_map(function ($tmpItem3) {
                    return $this->sanitizeItem($tmpItem3);
                }, $tmpItems3);

                preg_match(self::ENGINE_PATTERN, $tmpItems3[0], $engineMatches);

                if (!preg_match(self::ENGINE_PATTERN, $tmpItems3[0], $engineMatches) || !preg_match(self::TOW_LIMIT_PATTERN, $tmpItems3[0])) {
                    $defaultModel = $tmpItems3[0];

                    if ($make !== 'CHEVROLET/GMC' && $make !== 'FORD' && $make !== 'RAM') {
                        $defaultModel = ucfirst(strtolower($defaultModel));
                    }

                    unset($tmpItems3[0]);
                }

                foreach ($tmpItems3 as $tmpItem3) {
                    if (empty(trim($tmpItem3))) {
                        continue;
                    }

                    preg_match(self::ENGINE_PATTERN, $tmpItem3, $modelMatches);

                    $modelString = $modelMatches[1];
                    $engine = $modelMatches[2];

                    if ($defaultModel === null) {
                        $emptyReplacement = array_fill(0, count(self::VEHICLE_OPTIONS_MAPPING), '$1');
                        $model = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), $emptyReplacement, $modelString);
                        $model = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $model);
                    } else {
                        $model = $defaultModel;
                    }

                    $model = trim($model, '/-');

                    $driveTrains = [];

                    preg_match_all(self::DRIVE_TRAIN_PATTERN_ALL, $tmpItem3, $driveTrainMatches);

                    foreach ($driveTrainMatches[2] as $driveTrain) {
                        $driveTrains[] = strtr($driveTrain, self::DRIVE_TRAIN_REPLACE);
                    }

                    if (empty($driveTrains)) {
                        $driveTrains[] = null;
                    }

                    $subModel = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), self::VEHICLE_OPTIONS_MAPPING, $modelString);
                    $subModel = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $subModel);
                    $subModel = trim($subModel, '/-');

                    $towLimits = [];

                    preg_match(self::TOW_LIMIT_PATTERN, $tmpItem3, $towLimitsMatches);

                    foreach (explode('/', $towLimitsMatches[2]) as $towLimit) {
                        $towLimit = (int)str_replace(',', '', $towLimit);

                        if (!empty($towLimit)) {
                            $towLimits[] = $towLimit;
                        }
                    }

                    foreach ($driveTrains as $driveTrain) {
                        foreach ($towLimits as $towLimit) {
                            $items[] = [
                                'make' => $make,
                                'model' => $model,
                                'sub_model' => $subModel,
                                'engine' => $engine,
                                'drive_train' => $driveTrain,
                                'tow_limit' => $towLimit
                            ];
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @param string $item
     * @return string
     */
    private function sanitizeItem(string $item): string
    {
        $item = trim(preg_replace(self::BROKE_SYMBOLS_PATTERN, ")", $item));
        $item = trim(preg_replace(self::BROKE_SYMBOLS_PATTERN2, "", $item));

        return $item;
    }

    /**
     * @return mixed
     */
    private function addMakes()
    {
        $items = array_map(function ($make) {
            return ['name' => $make];
        }, $this->makes);

        return $this->makesRepository->create($items);
    }

    /**
     * @param $vehicles
     * @return mixed
     */
    private function addVehicles(array $vehicles)
    {
        return $this->vehiclesRepository->create($vehicles);
    }
}
