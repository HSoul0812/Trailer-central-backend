<?php

namespace Tests\database\seeds\Inventory;

use App\Models\Inventory\Category;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class CategorySeeder
 * @package Tests\database\seeds\Inventory
 *
 * @property-read Collection<CategorySeeder> $categories
 */
class CategorySeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Collection<CategorySeeder>
     */
    private $categories;

    /**
     * @var EntityTypeSeeder
     */
    private $entityTypeSeeder;

    public function __construct(array $params)
    {
        $this->params = $params;
        $this->entityTypeSeeder = new EntityTypeSeeder([]);
    }

    public function seed($count = 1): void
    {
        $this->entityTypeSeeder->seed($count);

        $this->categories = factory(Category::class, $count)->create([
            'entity_type_id' => $this->entityTypeSeeder->data()->random()->entity_type_id
        ] + $this->params);
    }

    public function data()
    {
        return $this->categories;
    }

    public function cleanUp(): void
    {
        Category::destroy($this->categories->map(function ($category) {
            return $category->inventory_category_id;
        }));

        $this->entityTypeSeeder->cleanUp();
    }
}
