<?php

namespace App\Services\Dms\Pos;

use App\Exceptions\Dms\Pos\RegisterException;
use App\Models\Pos\Register;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use Illuminate\Support\Facades\Log;

class RegisterService implements RegisterServiceInterface
{
    /**
     * @var RegisterRepositoryInterface
     */
    private $registerRepository;

    public function __construct(RegisterRepositoryInterface $registerRepository)
    {
        $this->registerRepository = $registerRepository;
    }

    /**
     * Validates and opens register for given outlet
     *
     * @param array $params
     * @return string
     * @throws RegisterException
     */
    public function open(array $params): string
    {
        if ($this->registerRepository->hasOpenRegister((int)$params['outlet_id'])) {
            Log::info('Register already opened for the outlet.', ['params' => $params]);

            return 'A register is already opened!';
        }

        try {
            $this->registerRepository->beginTransaction();

            $register = $this->registerRepository->create($params);

            if (!$register instanceof Register) {
                Log::error('Register hasn\'t been opened.', ['params' => $params]);
                $this->registerRepository->rollbackTransaction();

                throw new RegisterException('Register hasn\'t been opened');
            }
            $this->registerRepository->commitTransaction();

            Log::info('Register has been successfully opened for outlet.', ['register' => $register]);

        } catch (\Exception $exception) {
            Log::error('Register open error. Params - ' . json_encode($params), $exception->getTrace());
            $this->registerRepository->rollbackTransaction();

            throw new RegisterException('Register hasn\'t been opened');
        }

        return 'Register has been opened successfully!';
    }
}
