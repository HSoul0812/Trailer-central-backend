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
    private const ENGINE_PATTERN = '/(.*)?\s+((DAE)|(Electric)|(All except Hybrid)|(\(?all\)?)|(\(?All\)?)|([1-8]\.[0-9](L|l)(\s+HEV)?(\s+((V-(6|8|10))|(I-[4,6])|(H-[4,6])))?(\s+(TC|TD|HO)\/?){0,2}))\s+/';

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
        '/(\S+\s+)\/?WB\/?(\s+|$)/' => '$1Wheelbase$2',
        '/(\S+\s+)\/?EL\/?(\s+|$)/' => '$1Extra Length$2',
        '/(\S+\s+)\/?LR\/?(\s+|$)/' => '$1Low Roof$2',
        '/(\S+\s+)\/?MR\/?(\s+|$)/' => '$1Medium Roof$2',
        '/(\S+\s+)\/?HR\/?(\s+|$)/' => '$1High Roof$2',
        '/(\S+\s+)\/?EHR\/?(\s+|$)/' => '$1Extra-High Roof$2',
        '/(\S+\s+)\/?QC\/?(\s+|$)/' => '$1Quad Cab$2',
        '/(\S+\s+)\/?RWB\/?(\s+|$)/' => '$1Regular Wheelbase$2',
        '/(\S+\s+)\/?KC\/?(\s+|$)/' => '$1King Cab$2',
        '/(\S+\s+)\/?SC\/?(\s+|$)/' => '$1Supercharged$2',
        '/(\S+\s+)\/?SuperCab\/?(\s+|$)/' => '$1Super Cab$2',
        '/(\S+\s+)\/?SuperCrew\/?(\s+|$)/' => '$1Super Crew$2',
        '/(\S+\s+)\/?RCC\/?(\s+|$)/' => '$1Regular Chassis Cab$2',
        '/(\S+\s+)\/?SCC\/?(\s+|$)/' => '$1Super Chassis Cab$2',
        '/(\S+\s+)\/?CCC\/?(\s+|$)/' => '$1Crew Chassis Cab$2',
        '/(\S+\s+)\/?Reg\.\/?(\s+|$)/' => '$1Regular$2',
    ];

    private const DRIVE_TRAIN_REPLACE = [
        'AWD' => 'All-Wheel Drive',
        'FWD' => 'Front-Wheel Drive',
        'RWD' => 'Rear-Wheel Drive',
        '2WD' => 'Two-Wheel Drive',
        '4WD' => 'Four-Wheel Drive',
    ];

    private const ENGINE_OPTIONS_MAPPING = [
        '/(\S+\s+)DAE(\s+|$)/' => '$1Dual Asynchronous Electric$2',
        '/(\S+\s+)TC(\s+|$)/' => '$1Turbocharged$2',
        '/(\S+\s+)TD(\s+|$)/' => '$1Turbo Diesel$2',
        '/(\S+\s+)SC(\s+|$)/' => '$1Supercharged$2',
        '/(\S+\s+)TDI(\s+|$)/' => '$1Turbo Diesel,Intercooled$2',
        '/(\S+\s+)TCI(\s+|$)/' => '$1Turbocharged,Intercooled$2',
    ];

    private const TRANSMISSION_OPTIONS_MAPPING = [
        '/(\S+\s+)(Automatic Transmission)(\s+|$)/' => 'Automatic',
        '/(\S+\s+)(Manual Transmission)(\s+|$)/' => 'Manual',
    ];

    private const CHEVROLET_GMC_MODEL_PATTERN = '/^\s*(\S+)\/(\S+?(\s*XL)?)(\s+|$)/';

    private const TRANSMISSION_GEAR_RATIO_PATTERN = '/(\d+|\s+|\(|\)|,|\/|\*)%s(\s+|\(|\)|,|\/|$|\*)/';

    private const YEARS_TRANSMISSION_MAPPING = [
        2020 => [
            'a' => 'Automatic',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'a10' => 'Automatic, 10 speeds',
            'm' => 'Manual',
        ],
        2019 => [
            'a' => 'Automatic',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'a9' => 'Automatic, 9 Speeds',
            'a10' => 'Automatic, 10 speeds',
            'm' => 'Manual',
        ],
        2018 => [
            'a' => 'Automatic',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'a9' => 'Automatic, 9 Speeds',
            'a10' => 'Automatic, 10 speeds',
            'm' => 'Manual',
        ],
        2017 => [
            'a' => 'Automatic',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'm' => 'Manual',
            'm6' => 'Manual, 6 Speeds',
        ],
        2016 => [
            'a' => 'Automatic',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'm' => 'Manual',
            'm6' => 'Manual, 6 Speeds',
        ],
        2015 => [
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2014 => [
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2013 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'a8' => 'Automatic, 8 Speeds',
            'm' => 'Manual',
            'm5' => 'Manual, 5 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2012 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'm' => 'Manual',
            'm5' => 'Manual, 5 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2011 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'm' => 'Manual',
            'm5' => 'Manual, 5 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2010 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'm' => 'Manual',
            'm5' => 'Manual, 5 Speeds',
            'm6' => 'Manual, 6 Speeds',
        ],
        2009 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'm' => 'Manual',
        ],
        2008 => [
            'a' => 'Automatic',
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'a6' => 'Automatic, 6 Speeds',
            'm' => 'Manual',
        ],
        2007 => [
            'a' => 'Automatic',
            'm' => 'Manual',
        ],
        2006 => [
            'a4' => 'Automatic, 4 Speeds',
            'a5' => 'Automatic, 5 Speeds',
            'm' => 'Manual',
        ],
    ];

    private const YEARS_GEAR_RATIO_MAPPING = [
        2020 => [
            'c' => '3.15:1',
            'd' => '3.21:1',
            'f' => '3.31:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
        ],
        2019 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
        ],
        2018 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
        ],
        2017 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
        ],
        2016 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
        ],
        2015 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2014 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2013 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2012 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2011 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2010 => [
            'b' => '3.08:1',
            'c' => '3.15:1',
            'd' => '3.21:1',
            'e' => '3.23:1',
            'f' => '3.31:1',
            'g' => '3.42:1',
            'h' => '3.55:1',
            'i' => '3.73:1',
            'j' => '3.92:1',
            'k' => '4.10:1',
            'l' => '4.30:1',
            'n' => '4.44:1',
            'o' => '4.56:1',
            'q' => '4.88:1',
            'r' => '5.38:1',
        ],
        2008 => [
            'b' => '3.08:1',
            'c' => '3.21:1',
            'd' => '3.23:1',
            'e' => '3.42:1',
            'f' => '3.55:1',
            'g' => '3.73:1',
            'h' => '3.92:1',
            'i' => '4.10:1',
            'j' => '4.30:1',
            'k' => '4.44:1',
            'l' => '4.56:1',
            'n' => '4.88:1',
            'o' => '5.38:1',
        ],
        2007 => [
            'f' => '4.88:1',
            'g' => '5.38:1',
            'n' => '3.21:1',
            'o' => '3.23:1',
            'p' => '3.31:1',
            'q' => '3.42:1',
            'r' => '3.55:1',
            's' => '3.73:1',
            't' => '3.92:1',
            'u' => '4.10:1',
            'v' => '4.30:1',
        ],
    ];

    private const YEAR_PACKAGE_REQUIRED = [
        2020 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2019 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2018 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2017 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2016 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2015 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2014 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2013 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2012 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2011 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2010 => [
            'p' => 'payload_package_required',
            't' => 'towing_package_required',
        ],
        2009 => [
            't' => 'towing_package_required',
        ],
        2008 => [
            't' => 'towing_package_required',
        ],
        2007 => [
            'z' => 'towing_package_required',
        ],
        2006 => [
            't' => 'towing_package_required',
        ],
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

            if ($year <= 2005) {
                continue;
            }

            $items = $this->buildItems($file->getContents(), $year);

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
     * @param int $year
     * @return array
     */
    private function buildItems(string $content, int $year): array
    {
        $tmpItems = [];
        $items = [];

        foreach (preg_split("/(\n){3}/", $content) as $tmpItem2) {
            $tmpItem2 = $this->sanitizeItem($tmpItem2);
            $makeStr = strtoupper(trim(strstr($tmpItem2, PHP_EOL, true)));

            if ($makeStr !== 'CHEVROLET/GMC') {
                $makes = [$makeStr];
            } else {
                $makes = explode('/', $makeStr);
            }

            foreach ($makes as $make) {
                $make = trim($make);

                $tmpItems[$make] = isset($tmpItems[$make]) ? $tmpItems[$make] . PHP_EOL : '';
                $tmpItems[$make] .= trim(substr_replace($tmpItem2, '', strpos($tmpItem2, $makeStr), strlen($makeStr)));

                if (!in_array($make, $this->makes)) {
                    $this->makes[] = $make;
                }
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

                    if ($make !== 'CHEVROLET' && $make !== 'GMC' && $make !== 'FORD' && $make !== 'RAM') {
                        $defaultModel = ucfirst(strtolower($defaultModel));
                    }

                    unset($tmpItems3[0]);
                }

                foreach ($tmpItems3 as $tmpItem3) {
                    if (empty(trim($tmpItem3))) {
                        continue;
                    }

                    $towTypes = [''];
                    $transmissions = [''];
                    $gearRatios = [''];

                    $towLimitTransmissions = [];
                    $towLimitGearRatio = [];

                    $towingPackageRequired = false;
                    $payloadPackageRequired = false;

                    preg_match(self::ENGINE_PATTERN, $tmpItem3, $modelMatches);

                    $modelString = $modelMatches[1];

                    if (($make === 'CHEVROLET' || $make === 'GMC') && preg_match(self::CHEVROLET_GMC_MODEL_PATTERN, $modelString)) {
                        $modelReplacement = $make === 'CHEVROLET' ? '$1$4' : '$2$4';
                        $modelString = preg_replace(self::CHEVROLET_GMC_MODEL_PATTERN, $modelReplacement, $modelString);
                    }

                    $engine = preg_replace(array_keys(self::ENGINE_OPTIONS_MAPPING), self::ENGINE_OPTIONS_MAPPING, $modelMatches[2]);

                    if ($defaultModel === null) {
                        if ($make !== 'FORD' && $make !== 'CHEVROLET' && $make !== 'GMC' && $make !== 'RAM') {
                            $emptyReplacement = array_fill(0, count(self::VEHICLE_OPTIONS_MAPPING), '$1');
                            $model = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), $emptyReplacement, $modelString);
                            $model = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $model);
                        } else {
                            $model = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $modelString);
                            $model = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), self::VEHICLE_OPTIONS_MAPPING, $model);
                        }

                    } elseif ($make === 'FORD') {
                        $model = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $modelString);
                        $model = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), self::VEHICLE_OPTIONS_MAPPING, $model);

                        foreach (self::TRANSMISSION_OPTIONS_MAPPING as $pattern => $replacement) {
                            if (preg_match($pattern, $defaultModel)) {
                                $transmissions = [$replacement];
                            }
                        }

                        $emptyReplacementTransmission = array_fill(0, count(self::TRANSMISSION_OPTIONS_MAPPING), '$1$3');
                        $towTypesString = preg_replace(array_keys(self::TRANSMISSION_OPTIONS_MAPPING), $emptyReplacementTransmission, $defaultModel);

                        $towTypes = explode('/', $towTypesString);

                    } elseif ($make === 'CHEVROLET' || $make === 'GMC') {
                        $modelsArray = explode('//', $defaultModel);

                        $modelReplacement = $make === 'CHEVROLET' ? '$1$4' : '$2$4';
                        $modelString = ucfirst(strtolower(preg_replace(self::CHEVROLET_GMC_MODEL_PATTERN, $modelReplacement, $modelsArray[0]))) . ' ' . $modelString;
                        $model = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $modelString);
                        $model = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), self::VEHICLE_OPTIONS_MAPPING, $model);

                        if (isset($modelsArray[1])) {
                            foreach (self::TRANSMISSION_OPTIONS_MAPPING as $pattern => $replacement) {
                                if (preg_match($pattern, $modelsArray[1])) {
                                    $transmissions = [$replacement];
                                }
                            }

                            $emptyReplacementTransmission = array_fill(0, count(self::TRANSMISSION_OPTIONS_MAPPING), '$1$3');
                            $towTypesString = preg_replace(array_keys(self::TRANSMISSION_OPTIONS_MAPPING), $emptyReplacementTransmission, $modelsArray[1]);

                            $towTypes = explode('/', $towTypesString);
                        }
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
                        $driveTrains[] = '';
                    }

                    $subModel = preg_replace(array_keys(self::VEHICLE_OPTIONS_MAPPING), self::VEHICLE_OPTIONS_MAPPING, $modelString);
                    $subModel = preg_replace(self::DRIVE_TRAIN_PATTERN, '', $subModel);
                    $subModel = trim($subModel, '/-');

                    $towLimits = [];

                    preg_match(self::TOW_LIMIT_PATTERN, $tmpItem3, $towLimitsMatches);

                    foreach (explode('/', $towLimitsMatches[2]) as $towLimitMatch) {
                        $towLimit = (int)str_replace(',', '', $towLimitMatch);

                        if (!empty($towLimit) || $towLimit === 0) {
                            $towLimits[] = $towLimit;
                        }
                    }

                    if (isset(self::YEARS_TRANSMISSION_MAPPING[$year])) {
                        foreach (self::YEARS_TRANSMISSION_MAPPING[$year] as $keyTrans => $valueTrans) {
                            if (preg_match(sprintf(self::TRANSMISSION_GEAR_RATIO_PATTERN, $keyTrans), $towLimitsMatches[2])) {
                                $towLimitTransmissions[] = $valueTrans;
                            }
                        }
                    }

                    if (isset(self::YEARS_GEAR_RATIO_MAPPING[$year])) {
                        foreach (self::YEARS_GEAR_RATIO_MAPPING[$year] as $keyRatio => $valueRatio) {
                            if (preg_match(sprintf(self::TRANSMISSION_GEAR_RATIO_PATTERN, $keyRatio), $towLimitsMatches[2])) {
                                $towLimitGearRatio[] = $valueRatio;
                            }
                        }
                    }

                    if (isset(self::YEAR_PACKAGE_REQUIRED[$year])) {
                        foreach (self::YEAR_PACKAGE_REQUIRED[$year] as $keyPackage => $valuePackage) {
                            if (preg_match(sprintf(self::TRANSMISSION_GEAR_RATIO_PATTERN, $keyPackage), $towLimitsMatches[2])) {
                                if ($valuePackage === 'towing_package_required') {
                                    $towingPackageRequired = true;
                                } else {
                                    $payloadPackageRequired = true;
                                }
                            }
                        }
                    }

                    if (!empty($towLimitTransmissions)) {
                        $transmissions = $towLimitTransmissions;
                    }

                    if (!empty($towLimitGearRatio)) {
                        $gearRatios = $towLimitGearRatio;
                    }

                    foreach ($driveTrains as $driveTrain) {
                        foreach ($gearRatios as $gearRatio) {
                            foreach ($transmissions as $transmission) {
                                if ($towTypes > $towLimits) {
                                    $towLimits = array_pad($towLimits, count($towTypes), $towLimits[0]);
                                }

                                foreach ($towLimits as $towLimitKey => $towLimit) {
                                    $towType = $towTypes[$towLimitKey] ?? $towTypes[0];

                                    $items[] = [
                                        'year' => $year,
                                        'make' => trim($make),
                                        'model' => trim(str_replace('  ', ' ', $model)),
                                        'sub_model' => trim(str_replace('  ', ' ', $subModel)),
                                        'engine' => trim($engine),
                                        'drive_train' => trim($driveTrain),
                                        'tow_limit' => trim($towLimit),
                                        'tow_type' => trim(strtoupper($towType)),
                                        'transmission' => trim(ucfirst(strtolower($transmission))),
                                        'gear_ratio' => trim($gearRatio),
                                        'towing_package_required' => $towingPackageRequired,
                                        'payload_package_required' => $payloadPackageRequired,
                                    ];
                                }
                            }
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
