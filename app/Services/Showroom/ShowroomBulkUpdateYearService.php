<?php

namespace App\Services\Showroom;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Showroom\ShowroomBulkUpdateRepository;

class ShowroomBulkUpdateYearService implements ShowroomBulkUpdateYearServiceInterface
{

    /**
     * @var array
     */
    private $params;

    /**
     * @var ShowroomBulkUpdateRepository
     */
    private $manufacturerRepository;

    /**
     * @param array $params
     */
    public function __construct(array $params) {
        $this->params = $params;
        $this->manufacturerRepository = new ShowroomBulkUpdateRepository();
    }

    /**
     * Updates Showrooms year
     *
     * @throws Exception
     */
    public function update()
    {
        try {
            $manufacturers = $this->manufacturerRepository->get([
                'manufacturer' => $this->params['manufacturer'],
                'year' => $this->params['from']
            ]);

            foreach ($manufacturers as $key => $manufacturer) {
                $this->manufacturerRepository->bulkUpdate(
                    $manufacturer,
                    [
                        'year' => $this->params['to']
                    ]
                );
            }

            Log::info('Manufacturer years updated successfully', $this->params);
        } catch (Exception $e) {
            Log::error('Manufacturer year update error. Message - ' . $e->getMessage(), $e->getTrace());

            throw $e;
        }
    }

}
