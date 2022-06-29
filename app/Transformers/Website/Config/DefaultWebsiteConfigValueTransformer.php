<?php

declare(strict_types=1);

namespace App\Transformers\Website\Config;

use App\Models\Website\Config\WebsiteConfigDefault;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use League\Fractal\TransformerAbstract;

class DefaultWebsiteConfigValueTransformer extends TransformerAbstract
{
    /** @var WebsiteConfigRepositoryInterface */
    private $repository;

    /** @var int */
    private $websiteId;

    public function __construct(WebsiteConfigRepositoryInterface $repository, int $websiteId)
    {
        $this->repository = $repository;
        $this->websiteId = $websiteId;
    }

    public function transform(WebsiteConfigDefault $config): array
    {
        $value = $this->repository->getValueOfConfig($this->websiteId, $config->key);

        return [
            'key' => $config->key,
            'grouping' => $config->grouping ?: 'No group',
            'private' => (bool)$config->private,
            'type' => $config->type,
            'label' => $config->label,
            'default_label' => $config->default_label,
            'note' => $config->note,
            'values' => $config->values,
            'values_mapping' => $config->values_mapping,
            'default_value' => $config->default_value,
            'current_value' => $value ? $value->value : $config->default_value
        ];
    }
}
