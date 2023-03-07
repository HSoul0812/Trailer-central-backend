<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\CampaignException;
use App\Models\CRM\Email\Campaign;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class CampaignService
 * @package App\Services\CRM\Email
 */
class CampaignService implements CampaignServiceInterface
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(CampaignRepositoryInterface $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param array $params
     * @return Campaign
     * @throws CampaignException
     */
    public function create(array $params): Campaign
    {
        try {
            $this->campaignRepository->beginTransaction();

            $campaign = $this->campaignRepository->create($params);

            $this->campaignRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Campaign create error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->campaignRepository->rollbackTransaction();

            throw new CampaignException('Campaign create error');
        }

        return $campaign;
    }

    /**
     * @param array $params
     * @return Campaign
     * @throws CampaignException
     */
    public function update(array $params): Campaign
    {
        try {
            $this->campaignRepository->beginTransaction();

            $campaign = $this->campaignRepository->update($params);

            $this->campaignRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Campaign update error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->campaignRepository->rollbackTransaction();

            throw new CampaignException('Campaign update error');
        }

        return $campaign;
    }

    /**
     * @param array $params
     * @return bool
     * @throws CampaignException
     */
    public function delete(array $params): ?bool
    {
        try {
            $this->campaignRepository->beginTransaction();

            $result = $this->campaignRepository->delete($params);

            $this->campaignRepository->commitTransaction();
        } catch (\Exception $e) {
            Log::error('Campaign delete error. Message - ' . $e->getMessage(), $e->getTrace());
            $this->campaignRepository->rollbackTransaction();

            throw new CampaignException('Campaign delete error');
        }

        return $result;
    }
}
