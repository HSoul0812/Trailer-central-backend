<?php

namespace App\Services\CRM\Documents;

use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\MissingS3FileException;
use Illuminate\Support\Facades\Storage;
use App\Traits\S3\S3Helper;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Helpers\ImageHelper;

class DealerDocumentsService implements DealerDocumentsServiceInterface {

    use S3Helper;

    /**
     * @var DealerDocumentsRepositoryInterface
     */
    private $documentRepository;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(DealerDocumentsRepositoryInterface $documentRepository, ImageHelper $imageHelper)
    {
        $this->documentRepository = $documentRepository;

        $this->imageHelper = $imageHelper;
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function create(array $params): Collection
    {
        $documents = collect();

        foreach ($params['files'] as $file) {

            if (!($file instanceof UploadedFile)) continue;
            
            $fileContent = $file->get();
            $fileName = $file->getClientOriginalName();
            $randomS3Filename = $this->imageHelper->getRandomString($fileContent);

            Storage::disk('s3')->put($randomS3Filename, $fileContent);

            $document = $this->documentRepository->create([
                'lead_id' => $params['lead_id'],
                'dealer_id' => $params['dealer_id'],
                'filename' => $fileName,
                'full_path' => Storage::disk('s3')->url($randomS3Filename)
            ]);

            $documents->push($document);
        }

        return $documents;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): bool
    {
        $document = $this->documentRepository->find($params['document_id']);
        $filePath = parse_url($document->full_path, PHP_URL_PATH);

        $deleted = $this->documentRepository->delete($params);

        Storage::disk('s3')->delete($filePath);

        return $deleted;
    }
}