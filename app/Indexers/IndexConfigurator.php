<?php

namespace App\Indexers;

use ElasticAdapter\Indices\Mapping;
use ElasticAdapter\Indices\Settings;

abstract class IndexConfigurator
{
    /** @var Transformer */
    protected $transformer;

    /** @var Mapping|null */
    protected $mapping;

    /** @var Settings|null */
    protected $settings;

    public const PROPERTIES = [];

    abstract public function name(): string;

    public function transformer(): Transformer{
        if($this->transformer){
            return $this->transformer;
        }

        $this->transformer = new ModelTransformer();

        return $this->transformer;
    }

    public function mapping(): ?Mapping
    {
        if ($this->mapping) {
            return $this->mapping;
        }

        if (!empty(static::PROPERTIES)) {
            $this->mapping = new Mapping();

            foreach (static::PROPERTIES as $field => $settings) {
                // We need to find a way to set the `case_normal` normalizer, or use another ES7 feature which works in a similar way
                unset($settings['normalizer']);
                $this->mapping->{$settings['type']}($field, $settings);
            }

            return $this->mapping;
        }

        return $this->mapping;
    }

    public function settings(): ?Settings
    {
        return $this->settings;
    }
}
