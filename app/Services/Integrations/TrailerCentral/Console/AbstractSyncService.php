<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use App\Exceptions\CannotBeUsedBeyondConsole;
use App\Models\SyncProcess;
use App\Repositories\Integrations\TrailerCentral\SourceRepositoryInterface;
use App\Repositories\SyncProcessRepositoryInterface;
use App\Services\LoggerServiceInterface;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use JsonException;
use PDOException;
use Throwable;

abstract class AbstractSyncService
{
    protected ?SyncProcess $lastProcess = null;
    protected SyncProcess $currentProcess;
    protected bool $isNotTheFirstImport;
    protected int $numberOfRecordsImported;

    public function __construct(
        private SourceRepositoryInterface $sourceRepository,
        private LogServiceInterface $targetRepository,
        private SyncProcessRepositoryInterface $processRepository,
        private ApplicationContract $app,
        private LoggerServiceInterface $logger,
        private ConnectionInterface $connection
    ) {
    }

    /**
     * @throws PDOException        when some unknown PDO error has been thrown
     * @throws JsonException       when the metadata were unable to be serialized
     * @throws Exception|Throwable when some unknown exception has been thrown
     */
    public function sync(): int
    {
        if (!$this->app->runningInConsole()) {
            throw new CannotBeUsedBeyondConsole();
        }

        // needed to create an insertion buffer, surely it only will be at the very first time
        ini_set('memory_limit', $this->getMemoryLimit());

        $this->currentProcess = $this->processRepository->create(['name' => $this->getProcessName()]);

        try {
            $this->lastProcess = $this->processRepository->lastFinishedByProcessName($this->getProcessName());

            $this->numberOfRecordsImported = 0;

            $this->isNotTheFirstImport = $this->processRepository->isNotTheFirstImport($this->getProcessName());

            $this->connection->transaction(fn () => $this->applyToTransaction());

            return $this->numberOfRecordsImported;
        } catch (Exception|Throwable $exception) {
            $this->processRepository->failById($this->currentProcess->id, ['errorMessage' => $exception->getMessage()]);

            $this->logger->error(sprintf(
                '[SyncService::%s] process %d has failed due %s',
                $this->getProcessName(),
                $this->currentProcess->id,
                $exception->getMessage()
            )
            );

            throw $exception;
        }
    }

    abstract protected function getProcessName(): string;

    abstract protected function getChunkLimit(): int;

    abstract protected function getMemoryLimit(): string;

    /**
     * @throws PDOException  when some unknown PDO error has been thrown
     * @throws JsonException when the metadata were unable to be serialized
     * @throws Exception     when some unknown exception has been thrown
     */
    protected function applyToTransaction(): void
    {
        $this->sourceRepository
            ->queryAllSince($this->lastProcess?->finished_at)
            ->chunk($this->getChunkLimit(), fn ($records) => $this->applyToChuck($records));

        $this->processRepository->finishById(
            $this->currentProcess->id,
            ['numberOfRecordsImported' => $this->numberOfRecordsImported]
        );
    }

    /**
     * @throws PDOException  when some unknown PDO error has been thrown
     * @throws JsonException when the metadata were unable to be serialized
     * @throws Exception     when some unknown exception has been thrown
     */
    protected function applyToChuck(Collection $records): void
    {
        $insertValues = '';

        foreach ($records as $record) {
            $insertValues .= $this->targetRepository->mapToInsertString($record, $this->isNotTheFirstImport);

            ++$this->numberOfRecordsImported;
        }

        $this->targetRepository->execute($insertValues);

        $this->logger->info(sprintf(
            '[SyncService::%s] %d records imported on process %d',
            $this->getProcessName(),
            $this->numberOfRecordsImported,
            $this->currentProcess->id
        )
        );
    }
}
