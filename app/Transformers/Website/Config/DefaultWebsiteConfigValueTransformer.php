<?php

declare(strict_types=1);

namespace App\Transformers\Website\Config;

use App\Models\Website\Config\WebsiteConfigDefault;
use App\Repositories\Website\Config\AvailableValues\WebsiteConfigDefaultAvailableValuesRepositoryInterface;
use League\Fractal\TransformerAbstract;

class DefaultWebsiteConfigValueTransformer extends TransformerAbstract
{
    /** @var int */
    private $websiteId;

    /** @var WebsiteConfigDefaultAvailableValuesRepositoryInterface */
    private $repository;

    public function __construct(WebsiteConfigDefaultAvailableValuesRepositoryInterface $repository, int $websiteId)
    {
        $this->repository = $repository;
        $this->websiteId = $websiteId;
    }

    public function transform(WebsiteConfigDefault $config): array
    {
        return [
            'key' => $config->key,
            'grouping' => $config->grouping ?: 'No group',
            'private' => (bool)$config->private,
            'type' => $config->type,
            'sort_order' => $config->sort_order,
            'label' => $config->label,
            'default_label' => $config->default_label,
            'note' => $config->note,
            'values' => $this->repository->getCustomAvailableValuesFor($config, $this->websiteId),
            'values_mapping' => $config->values_mapping,
            'default_value' => $config->default_value,
            'current_value' => $config->getValueAccordingWebsite($this->websiteId)
        ];
    }
}
