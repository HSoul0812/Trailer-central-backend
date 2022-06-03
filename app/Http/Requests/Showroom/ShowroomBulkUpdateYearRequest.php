<?php

namespace App\Http\Requests\Showroom;

use App\Http\Requests\Manufacturer\Manufacturers;
use App\Http\Requests\Request;
use App\Repositories\Showroom\ShowroomBulkUpdateRepositoryInterface;
use function App\Http\Requests\Inventory\Manufacturers\app;

class ShowroomBulkUpdateYearRequest extends Request
{
    /**
     * @var Manufacturers
     */
    private $manufacturer;

    protected $rules = [
        'manufacturer' => 'string|required',
        'from' => 'string|required',
        'to' => 'string|required'
    ];

    public function getManufacturer(): ?Manufacturers
    {
        if ($this->manufacturer === null) {
            $manufacturer = $this->getRepository()->findByToken($this->get('token'));

            if ($manufacturer !== null && $manufacturer->dealer_id !== $this->get('dealer_id')) {
                return null; // It is a token from  other dealer
            }

            $this->manufacturer = $manufacturer;
        }

        return $this->manufacturer;
    }

    protected function getRepository(): ShowroomBulkUpdateRepositoryInterface
    {
        return app(ShowroomBulkUpdateRepositoryInterface::class);
    }
}
