<?php

namespace App\Http\Controllers\v1\ViewsAndImpressions;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\ViewsAndImpressions\DownloadTTAndAffiliateZipFileRequest;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadTTAndAffiliateMonthlyCountingController extends AbstractRestfulController
{
    // Example: 2023/04/dealer-id-520.csv.gz
    public const FILE_PATH_REGEX = '/(?<year>\d*)\/(?<month>\d*)\/dealer-id-(?<dealer_id>\d*).*/';

    private FilesystemAdapter $storage;

    public function __construct()
    {
        parent::__construct();

        $this->storage = Storage::disk('monthly-inventory-impression-countings-reports');
    }

    public function index(IndexRequestInterface $request): StreamedResponse
    {
        $request->validate();

        $filePath = $request->input('file_path');

        preg_match(self::FILE_PATH_REGEX, $filePath, $matches, PREG_OFFSET_CAPTURE);

        $fileName = basename($filePath);

        if (isset($matches['year']) && isset($matches['month']) && isset($matches['dealer_id'])) {
            $fileName = sprintf('%d-%02d-%d.csv.gz', $matches['dealer_id'][0], $matches['month'][0], $matches['year'][0]);
        }

        return response()->streamDownload(
            fn () => $this->storage->readStream($filePath),
            $fileName,
        );
    }

    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(DownloadTTAndAffiliateZipFileRequest::class);
        });
    }
}
