<?php

namespace App\Services\Inventory;

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Cache;
use JetBrains\PhpStorm\ArrayShape;

trait CategoryMappingHelpers
{
    #[ArrayShape(['key' => 'string', 'type_id' => 'int'])]
    private function mapOldCategoryToNew($oldCategory): array
    {
        return Cache::remember('category/' . $oldCategory, 300, function () use ($oldCategory) {
            $defaultCategory = [
                'name' => 'Other',
                'type_id' => 1,
                'type_label' => 'General Trailers',
            ];
            $value = [];
            $mappedCategories = CategoryMappings::where('map_to', 'like', '%' . $oldCategory . '%')->get();
            $mappedCategory = null;

            foreach ($mappedCategories as $currentCategory) {
                $mapToCategories = explode(';', $currentCategory->map_to);
                foreach ($mapToCategories as $mapToCategory) {
                    if ($mapToCategory === $oldCategory) {
                        $mappedCategory = $currentCategory;
                    }
                }
            }

            if ($mappedCategory && $mappedCategory->category) {
                $value['key'] = $mappedCategory->map_from;
                $value['type_id'] = $mappedCategory->category->types[0]->id;
                $value['type_label'] = $mappedCategory->category->types[0]->name;
            } else {
                $value['key'] = $defaultCategory['name'];
                $value['type_id'] = $defaultCategory['type_id'];
                $value['type_label'] = $defaultCategory['type_label'];
            }

            return $value;
        });
    }

    private function getMappedCategories(?int $typeId, ?string $categoriesString): string
    {
        if (isset($typeId)) {
            $type = Type::find($typeId);
            $mappedCategories = '';
            if ($categoriesString) {
                $categoriesArray = explode(';', $categoriesString);
                $categories = $type->categories()->whereIn('name', $categoriesArray)->get();
            } else {
                $categories = $type->categories;
            }

            foreach ($categories as $category) {
                if ($category->category_mappings) {
                    $mappedCategories = $mappedCategories . $category->category_mappings->map_to . ';';
                }
            }
        } else {
            $mappedCategories = '';
            foreach (CategoryMappings::all() as $mapping) {
                $mappedCategories = $mappedCategories . $mapping->map_to . ';';
            }
        }

        return rtrim($mappedCategories, ';');
    }
}
