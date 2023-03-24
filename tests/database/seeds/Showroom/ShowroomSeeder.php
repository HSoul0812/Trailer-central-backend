<?php

declare(strict_types=1);

namespace Tests\database\seeds\Showroom;

use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomFeature;
use App\Models\Showroom\ShowroomFile;
use App\Models\Showroom\ShowroomGenericMap;
use App\Models\Showroom\ShowroomImage;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read Showroom $showroom
 * @property-read ShowroomGenericMap $showroomGenericMaps
 */
class ShowroomSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Showroom
     */
    private $showroom;

    /**
     * @var ShowroomFeature[]
     */
    private $showroomFeatures = [];

    /**
     * @var int
     */
    private $showroomFeaturesCount;

    /**
     * @var ShowroomFile[]
     */
    private $showroomFiles = [];

    /**
     * @var int
     */
    private $showroomFilesCount;

    /**
     * @var ShowroomGenericMap[]
     */
    private $showroomGenericMaps = [];

    /**
     * @var int
     */
    private $showroomGenericMapsCount;

    /**
     * @var ShowroomImage[]
     */
    private $showroomImages = [];

    /**
     * @var int
     */
    private $showroomImagesCount;

    /**
     * @var array
     */
    private $params;

    public function __construct($params = [])
    {
        $this->showroomFeaturesCount = $params['showroomFeaturesCount'] ?? 0;
        $this->showroomFilesCount = $params['showroomFilesCount'] ?? 0;
        $this->showroomGenericMapsCount = $params['showroomGenericMapsCount'] ?? 0;
        $this->showroomImagesCount = $params['showroomImagesCount'] ?? 0;
    }

    public function seed(): void
    {
        $this->showroom = factory(Showroom::class)->create([
            "manufacturer" => "Testing Showroom",
            "year" => 2022,
            "engine_type" => "",
            "pull_type" => "bumper",
            "pull_type_extra" => "",
            "description" => " Standard Equipment\r\n\r\n    Coupler: 2\" RAM\r\n    Jack: 2000#\r\n    Fenders: 9x72 Rolled Straight\r\n    Floor: 2\" Treated Pine\r\n    Brake: Electric 2 Wheel\r\n    Break Away Unit w/ Charger\r\n    Stake Pockets\r\n    Standard Colors:\r\n    Black, Red, Blue, and Charcoal\r\n\r\n",
            "description_txt" => "Standard Equipment  \n  \n Coupler: 2\" RAM  \n Jack: 2000#  \n Fenders: 9x72 Rolled Straight  \n Floor: 2\" Treated Pine  \n Brake: Electric 2 Wheel  \n Break Away Unit w/ Charger  \n Stake Pockets  \n Standard Colors:  \n Black, Red, Blue, and Charcoal",
            "description_html" => "",
            "lq_price" => 0.00,
            "is_visible" => 1,
            "NEED_REVIEW" => 1,
        ]);

        ShowroomFeature::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomFile::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomGenericMap::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomImage::where('showroom_id', '=', $this->showroom->getKey())->delete();

        for ($i = 0; $i < $this->showroomFeaturesCount; $i++) {
            $this->showroomFeatures[] = factory(ShowroomFeature::class)->create(['showroom_id' => $this->showroom->getKey()]);
        }

        for ($i = 0; $i < $this->showroomFilesCount; $i++) {
            $this->showroomFiles[] = factory(ShowroomFile::class)->create(['showroom_id' => $this->showroom->getKey()]);
        }

        for ($i = 0; $i < $this->showroomGenericMapsCount; $i++) {
            $this->showroomGenericMaps[] = factory(ShowroomGenericMap::class)->create(['showroom_id' => $this->showroom->getKey()]);
        }

        for ($i = 0; $i < $this->showroomImagesCount; $i++) {
            $this->showroomImages[] = factory(ShowroomImage::class)->create(['showroom_id' => $this->showroom->getKey()]);
        }
    }

    public function cleanUp(): void
    {
        ShowroomImage::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomFeature::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomFile::where('showroom_id', '=', $this->showroom->getKey())->delete();
        ShowroomGenericMap::where('showroom_id', '=', $this->showroom->getKey())->delete();
        Showroom::where('id', '=', $this->showroom->getKey())->delete();
    }

    public function getShowroomId(): int
    {
        return $this->showroom->getKey();
    }
}
