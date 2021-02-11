<?php

declare(strict_types=1);

namespace App\Http\Requests\Jobs;

use App\Http\Requests\Request;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;

class GetMonitoredJobsRequest extends Request
{
    /**
     * @var MonitoredJob
     */
    private $job;

    public function getRules(): array
    {
        return [
            'dealer_id' => 'required|integer',
            'token' => ['uuid', $this->validaTokenBelongsToDealer()]
        ];
    }

    public function getJob(): ?MonitoredJob
    {
        if ($this->job === null) {
            $job = $this->getRepository()->findByToken($this->get('token'));

            if ($job !== null && $job->dealer_id !== $this->get('dealer_id')) {
                return null; // It is a token from  other dealer
            }

            $this->job = $job;
        }

        return $this->job;
    }

    protected function getRepository(): MonitoredJobRepositoryInterface
    {
        return app(MonitoredJobRepositoryInterface::class);
    }

    protected function validaTokenBelongsToDealer(): callable
    {
        return function (string $attribute, $value, callable $fail): void {
            if ($value && $this->getJob() === null) {
                $fail('The job was not found.');
            }
        };
    }
}
