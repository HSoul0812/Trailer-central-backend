<?php

declare(strict_types=1);

namespace App\Services\Dms\Pos;

use App\Contracts\LoggerServiceInterface;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Services\Common\LoggerService;

class RegisterService implements RegisterServiceInterface
{
    /**
     * @var RegisterRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerService
     */
    private $logger;

    public function __construct(
        RegisterRepositoryInterface $registerRepository,
        LoggerServiceInterface $loggerService
    )
    {
        $this->repository = $registerRepository;
        $this->logger = $loggerService;
    }

    /**
     * Validates and opens register for given outlet
     *
     * @param array $params
     * @return bool
     */
    public function open(array $params): bool
    {
        if ($this->repository->hasOpenRegister((int)$params['outlet_id'])) {
            return true;
        }

        try {
            return $this->repository->create($params);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Register open error. Message - ' . $exception->getMessage() ,
                $exception->getTrace()
            );
            return false;
        }
    }
}
