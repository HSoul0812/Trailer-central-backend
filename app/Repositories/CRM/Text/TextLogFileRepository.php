<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Interactions\TextLogFile;
use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TextLogRepository
 * @package App\Repositories\CRM\Text
 */
class TextLogFileRepository extends RepositoryAbstract implements TextLogFileRepositoryInterface
{
    /**
     * @param $params
     * @return void
     */
    public function getAll($params): Collection
    {
        if (empty($params['dealer_texts_log_id'])) {
            throw new RepositoryInvalidArgumentException('dealer_texts_log_id has been missed. Params - ' . json_encode($params));
        }

        return TextLogFile::query()->where('dealer_texts_log_id', $params['dealer_texts_log_id'])->get();
    }
}
