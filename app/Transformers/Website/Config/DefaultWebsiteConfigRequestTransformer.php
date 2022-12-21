<?php

declare(strict_types=1);

namespace App\Transformers\Website\Config;

use App\Http\Requests\Website\Config\CreateOrUpdateRequest;
use League\Fractal\TransformerAbstract;

class DefaultWebsiteConfigRequestTransformer extends TransformerAbstract
{
    public function transform(CreateOrUpdateRequest $rawRequest): array
    {
        $request = $rawRequest->all();

        if (isset($request['general/head_script'])) {
            $request['general/head_script'] = base64_encode($request['general/head_script']);
        }

        return $request;
    }
}
