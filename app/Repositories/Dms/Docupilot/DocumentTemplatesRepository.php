<?php

namespace App\Repositories\Dms\Docupilot;

use App\Models\CRM\Dms\Docupilot\DocumentTemplates;
use App\Repositories\RepositoryAbstract;
use Illuminate\Support\Collection;

/**
 * Class TemplatesRepository
 * @package App\Repositories\Dms\Docupilot
 */
class DocumentTemplatesRepository extends RepositoryAbstract implements DocumentTemplatesRepositoryInterface
{
    /**
     * @param array $params
     */
    public function getAll($params): Collection
    {
        $query = DocumentTemplates::query();
        $query = $query->where(['dealer_id' => $params['dealer_id']]);

        if (isset($params['type'])) {
            $query = $query->where(['type' => $params['type']]);
        }

        if (isset($params['type_service'])) {
            $query = $query->where(['type_service' => $params['type_service']]);
        }

        return $query->get();
    }

    /**
     * @param array $params
     */
    public function get($params): DocumentTemplates
    {
        return DocumentTemplates::query()
            ->where(['dealer_id' => $params['dealer_id']])
            ->where(['template_id' => $params['template_id']])
            ->first();
    }

    /**
     * @param array $params
     * @return DocumentTemplates
     */
    public function update($params): DocumentTemplates
    {
        $query = DocumentTemplates::query()
            ->where(['template_id' => $params['template_id']])
            ->where(['dealer_id' => $params['dealer_id']]);

        $item = $query->firstOrFail();

        $item->update($params);

        return $item;
    }
}
