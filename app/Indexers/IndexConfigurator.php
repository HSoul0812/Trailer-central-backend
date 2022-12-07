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

    abstract public function aliasName(): string;

    public function shouldMakeAlias(): bool
    {
        return $this->name() !== $this->aliasName();
    }

    public function transformer(): Transformer
    {
        if ($this->transformer) {
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
