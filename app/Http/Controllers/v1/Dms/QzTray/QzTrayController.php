<?php

namespace App\Http\Controllers\v1\Dms\QzTray;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\QzTray\GetQzSignatureRequest;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class QzTrayController extends RestfulControllerV2
{
    /** @var string The file name that keeps the digital certificate for QZ Tray */
    const FILE_NAME_DIGITAL_CERTIFICATE = 'digital-certificate.txt';

    /** @var string The file name that keeps the private key for QZ Tray */
    const FILE_NAME_PRIVATE_KEY = 'private-key.pem';

    /** @var FilesystemAdapter */
    private $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('qz_tray');
    }

    public function digitalCert(): Response
    {
        try {
            return $this->response->array([
                'digital_certificate' => $this->storage->get(self::FILE_NAME_DIGITAL_CERTIFICATE),
            ]);
        } catch (FileNotFoundException $e) {
            $this->response->error('Digital Certificate file does not exist.', SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     */
    public function signature(Request $request)
    {
        $request = new GetQzSignatureRequest($request->all());

        $request->validate();

        try {
            $privateKey = openssl_get_privatekey($this->storage->get(self::FILE_NAME_PRIVATE_KEY));
        } catch (FileNotFoundException $e) {
            $this->response->error('Private key file does not exist.', SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $success = openssl_sign($request->input('to_sign'), $signature, $privateKey);

        if (!$success) {
            $this->response->error('Cannot generate the signature from the given data.', SymfonyResponse::HTTP_BAD_REQUEST);
        }

        return $this->response->array([
            'signature' => base64_encode($signature),
        ]);
    }
}
