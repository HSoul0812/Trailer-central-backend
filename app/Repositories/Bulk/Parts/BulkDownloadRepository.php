<?php


namespace App\Repositories\Bulk\Parts;


use App\Models\Bulk\Parts\BulkDownload;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;

class BulkDownloadRepository implements BulkDownloadRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function find($id)
    {
        return BulkDownload::find($id);
    }

    /**
     * @inheritDoc
     */
    public function create($params)
    {
        return BulkDownload::create($params);
    }

    /**
     * @param BulkDownload|int $download
     * @return bool
     */
    public function setCompleted($download)
    {
        // is it a BulkDownload?
        if ($download instanceof BulkDownload) {
            return $download->save();
        }

        // no, it is an $id
        return BulkDownload::where('id', $download)->update(['status' => BulkDownload::STATUS_COMPLETED]);
    }

}
