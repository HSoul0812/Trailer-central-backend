<?php

namespace App\Utilities\Scout;

use ElasticAdapter\Indices\Mapping;

abstract class SearchableMapper implements \App\Contracts\Scout\SearchableMapper
{
    public const FIELDS = [];

    public function mapping(): ?Mapping
    {
        if (!empty(static::FIELDS)) {
            $mapper = new Mapping();

            foreach (static::FIELDS as $field => $settings) {
                // We need to find a way to set the `case_normal` normalizer, or use another ES7 feature which works in a similar way
                unset($settings['normalizer']);
                $mapper->{$settings['type']}($field, $settings);
            }

            return $mapper;
        }

        return null;
    }
}
