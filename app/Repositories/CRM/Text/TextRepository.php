<?php

namespace App\Repositories\CRM\Text;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\CRM\Interactions\TextLogFile;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Text\Stop;
use App\Services\CRM\Text\TwilioServiceInterface;
use Carbon\Carbon;

class TextRepository implements TextRepositoryInterface
{
    use Transaction;

    private $sortOrders = [
        'date_sent' => [
            'field' => 'date_sent',
            'direction' => 'DESC'
        ],
        '-date_sent' => [
            'field' => 'date_sent',
            'direction' => 'ASC'
        ]
    ];

    /**
     * @param $params
     * @return TextLog
     */
    public function create($params): TextLog
    {
        $fileObjs = [];

        foreach ($params['files'] ?? [] as $file) {
            $fileObjs[] = new TextLogFile($file);
        }

        unset($params['files']);

        $textLog = new TextLog($params);
        $textLog->save();

        if (!empty($fileObjs)) {
            $textLog->files()->saveMany($fileObjs);
        }

        if ($textLog->interactionMessage) {
            $textLog->interactionMessage->searchable();
        }

        return $textLog;
    }

    public function delete($params) {
        // Mark Text Log as Deleted
        return TextLog::findOrFail($params['id'])->fill(['deleted' => '1'])->save();
    }

    public function get($params) {
        return TextLog::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Template::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        if (isset($params['lead_id'])) {
            $query = $query->where('lead_id', $params['lead_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $text = TextLog::findOrFail($params['id']);

        DB::transaction(function() use (&$text, $params) {
            // Fill Text Details
            $text->fill($params)->save();
        });

        return $text;
    }

    /**
     * Stop Processing Text Repository
     *
     * @param array $params
     * @return Stop
     */
    public function stop($params) {
        // Add Type if Empty
        if(!isset($params['type'])) {
            $params['type'] = Stop::REPORT_TYPE_DEFAULT;
        }

        // Insert Stop
        return Stop::create($params);
    }

    /**
     * Add Sort Query
     *
     * @param type $query
     * @param type $sort
     * @return type
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    /**
     * @param array $params
     * @return bool
     */
    public function bulkUpdate(array $params): bool
    {
        if ((empty($params['ids']) || !is_array($params['ids'])) && (empty($params['search']) || !is_array($params['search']))) {
            throw new RepositoryInvalidArgumentException('ids or search param has been missed. Params - ' . json_encode($params));
        }

        $query = TextLog::query();

        if (!empty($params['ids']) && is_array($params['ids'])) {
            $query->whereIn('id', $params['ids']);
            unset($params['ids']);
        }

        if (!empty($params['search']['lead_id'])) {
            $query->where('lead_id', $params['search']['lead_id']);
            unset($params['search']['lead_id']);
        }

        /** @var TextLog<Collection> $textLogs */
        $textLogs = $query->get();

        foreach ($textLogs as $textLog) {
            $textLog->update($params);
        }

        return true;
    }

    /**
     * @param string $fromNumber
     * @param string $toNumber
     * @return Collection
     */
    public function findByFromNumberToNumber(string $fromNumber, string $toNumber): Collection
    {
        $query = TextLog::query();

        $query->where([
            'from_number' => $fromNumber,
            'to_number' => $toNumber,
        ]);

        $query->with('lead');

        return $query->get();
    }
}
