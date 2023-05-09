<?php

declare(strict_types=1);

namespace App\Http\Requests\Website\Config;

use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Http\Requests\WithDealerRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use App;

/**
 * @property-read integer $website_id
 */
class PutExtraWebsiteConfigRequest extends WithDealerRequest
{
    /** ShowroomRepositoryInterface */
    private $showroomRepository;

    protected function getRules(): array
    {
        return array_merge(parent::getRules(), [
            'website_id' => 'integer|min:1|required|exists:website,id,dealer_id,' . $this->dealer_id,
            'include_showroom' => 'boolean',
            'showroom_dealers' => 'array',
            'global_filter'=> ['nullable', 'string'] // too complex to be validated, maybe in the near future this could be validated
        ]);
    }

    protected function dealerList(): Collection
    {
        return $this->getShowroomRepository()->distinctByManufacturers();
    }

    protected function getShowroomRepository(): ShowroomRepositoryInterface
    {
        if ($this->showroomRepository) {
            return $this->showroomRepository;
        }

        $this->showroomRepository = App::make(ShowroomRepositoryInterface::class);

        return $this->showroomRepository;
    }
}
