<?php

namespace App\Http\Requests\ViewsAndImpressions;

use App\Domains\ViewsAndImpressions\ValidationRules\ExistingMonthlyImpressionCountingZipFile;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;

class DownloadTTAndAffiliateZipFileRequest extends Request implements IndexRequestInterface
{
    protected function getRules(): array
    {
        return [
            'file_path' => [
                'required',
                'string',
                new ExistingMonthlyImpressionCountingZipFile(),
            ],
        ];
    }
}
