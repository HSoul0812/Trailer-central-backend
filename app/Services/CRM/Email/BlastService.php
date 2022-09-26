<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\BlastException;
use App\Models\CRM\Email\Blast;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class BlastService
 * @package App\Services\CRM\Email
 */
class BlastService implements BlastServiceInterface
{
    /**
     * @var BlastRepositoryInterface
     */
    private $blastRepository;

    /**
     * @param BlastRepositoryInterface $blastRepository
     */
    public function __construct(BlastRepositoryInterface $blastRepository)
    {
        $this->blastRepository = $blastRepository;
    }

    /**
     * @param array $params
     * @return Blast
     * @throws BlastException
     */
    public function create(array $params): Blast
    {
        try {
            $this->blastRepository->beginTransaction();



            $campaign = $this->blastRepository->create($params);

            $this->blastRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Blast create error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->blastRepository->rollbackTransaction();

            throw new BlastException('Blast create error');
        }

        return $campaign;
    }

    /**
     * @param array $params
     * @return Blast
     * @throws BlastException
     */
    public function update(array $params): Blast
    {
        try {
            $this->blastRepository->beginTransaction();

            $campaign = $this->blastRepository->update($params);

            $this->blastRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Blast update error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->blastRepository->rollbackTransaction();

            throw new BlastException('Blast update error');
        }

        return $campaign;
    }

    /**
     * @param array $params
     * @return bool
     * @throws BlastException
     */
    public function delete(array $params): ?bool
    {
        try {
            $this->blastRepository->beginTransaction();

            $result = $this->blastRepository->delete($params);

            $this->blastRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Blast delete error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->blastRepository->rollbackTransaction();

            throw new BlastException('Blast delete error');
        }

        return $result;
    }
}
