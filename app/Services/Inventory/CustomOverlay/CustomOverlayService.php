<?php

declare(strict_types=1);

namespace App\Services\Inventory\CustomOverlay;

use App\Repositories\Inventory\CustomOverlay\CustomOverlayRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;

class CustomOverlayService implements CustomOverlayServiceInterface
{
    /** @var CustomOverlayRepositoryInterface */
    private $repository;

    /** @var ConnectionInterface */
    private $connection;

    public function __construct(CustomOverlayRepositoryInterface $repository, ConnectionInterface $connection)
    {
        $this->repository = $repository;
        $this->connection = $connection;
    }

    public function list(int $dealerId): Collection
    {
        return $this->repository->getAll(['dealer_id' => $dealerId]);
    }

    public function upsert(int $dealerId, string $name, string $value): bool
    {
        return $this->repository->upsert(['dealer_id' => $dealerId, 'name' => $name, 'value' => $value]);
    }

    /**
     * @param int $dealerId
     * @param array{name: string, value: string} $overlays
     * @return bool
     * @throws \Throwable when some database error occurs
     * @throws \Throwable when some overlay has not a name
     */
    public function bulkUpsert(int $dealerId, array $overlays): bool
    {
        return $this->connection->transaction(function () use ($dealerId, $overlays): bool {
            foreach ($overlays as $overlay) {
                $this->upsert($dealerId, $overlay['name'], $overlay['value'] ?? '');
            }

            return true;
        });
    }
}
