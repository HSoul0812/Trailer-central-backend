<?php


namespace Tests\database\seeds\User;


use App\Models\User\Location\Geolocation;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

class GeolocationSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Geolocation
     */
    private $location;
    private $params;
    public function __construct(array $params = []) {
        $this->params = $params;
    }

    public function seed(): void {
        $this->location = factory(Geolocation::class)->create($this->params);
    }
    public function cleanUp(): void
    {
        $this->location->delete();
    }
}
