<?php

namespace App\Http\Controllers\v1\ViewsAndImpressions;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\ViewsAndImpressions\DownloadTTAndAffiliateZipFileRequest;
use Arr;
use Illuminate\Filesystem\FilesystemAdapter;
use JetBrains\PhpStorm\NoReturn;
use Storage;

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

    #[NoReturn]
    public function index(IndexRequestInterface $request): void
    {
        $request->validate();

        $filePath = $request->input('file_path');

        preg_match(self::FILE_PATH_REGEX, $filePath, $matches, PREG_OFFSET_CAPTURE);

        $fileName = basename($filePath);

        if (isset($matches['year']) && isset($matches['month']) && isset($matches['dealer_id'])) {
            $fileName = sprintf('%d-%02d-%d.csv.gz', $matches['dealer_id'][0], $matches['month'][0], $matches['year'][0]);
        }

        $this->downloadFile($filePath, $fileName);
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

    #[NoReturn]
    private function downloadFile(string $filePath, string $fileName): void
    {
        // For some reason, our backend staging server doesn't let us use the response()->download()
        // from Laravel, it causes the issue where the download file is 0 bytes in size

        $serverProtocol = Arr::get($_SERVER, 'SERVER_PROTOCOL', 'HTTP/1.1');

        header("$serverProtocol 200 OK");
        header('Cache-Control: public');
        header('Content-Type: application/gzip');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length:' . filesize($this->storage->path($filePath)));
        header("Content-Disposition: attachment; filename=$fileName");

        readfile($this->storage->path($filePath));

        exit;
    }
}
