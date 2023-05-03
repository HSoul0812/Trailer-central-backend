<?php

namespace App\Services\SysConfig;

use App\Repositories\SysConfig\SysConfigRepositoryInterface;

class SysConfigService implements SysConfigServiceInterface
{
    public function __construct(
        private SysConfigRepositoryInterface $sysConfigRepository
    ) {
    }

    public function list(): array
    {
        $configs = $this->sysConfigRepository->getAll([]);
        $configArr = [];
        foreach ($configs as $config) {
            $keyParts = explode('/', $config['key']);
            $end = array_pop($keyParts);
            $section = $keyParts[0];

            $subProp = &$configArr;
            foreach ($keyParts as $part) {
                if (!isset($subProp[$part])) {
                    $subProp[$part] = [];
                }
                $subProp = &$subProp[$part];
            }

            if ($section === 'filter') {
                $subProp[$end] = intval($config['value']);
            } else {
                $subProp[$end] = $config['value'];
            }
        }

        return $configArr;
    }
}
