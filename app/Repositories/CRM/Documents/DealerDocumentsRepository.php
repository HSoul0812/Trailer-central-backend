<?php

namespace App\Repositories\CRM\Documents;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Documents\DealerDocuments;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DealerDocumentsRepository
 * @package App\Repositories\CRM\Dealer
 */
class DealerDocumentsRepository extends RepositoryAbstract implements DealerDocumentsRepositoryInterface
{
    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        if (!isset($params['lead_id'])) {
            throw new RepositoryInvalidArgumentException('dealer_id and lead_id params have been missed. Params - ' . json_encode($params));
        }

        $query = DealerDocuments::query();
        $query->where('lead_id', '=', $params['lead_id']);

        return $query->get();
    }

    /**
     * @param int $id
     * @return DealerDocuments
     */
    public function find($id): DealerDocuments
    {
        return DealerDocuments::find($id);
    }

    /**
     * @param array $params
     * @return DealerDocuments
     */
    public function create($params): DealerDocuments
    {
        return DealerDocuments::create($params);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function delete($params)
    {
        return $this->find($params['document_id'])->delete();
    }
}
