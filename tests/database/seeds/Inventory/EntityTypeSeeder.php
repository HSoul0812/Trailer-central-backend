<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\EntityType;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class EntityTypeSeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read Collection<EntityType> $entityType
 */
class EntityTypeSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Collection<EntityType>
     */
    private $entityType;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function seed($count = 1): void
    {
        $this->entityType = factory(EntityType::class, $count)->create($this->params);
    }

    public function data()
    {
        return $this->entityType;
    }

    public function cleanUp(): void
    {
        EntityType::destroy($this->entityType->map(function ($type) {
            return $type->entity_type_id;
        }));
    }
}
