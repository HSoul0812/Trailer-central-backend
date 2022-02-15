<?php


namespace App\Services\Inventory\Packages;

use App\Exceptions\Inventory\Packages\PackageException;
use App\Models\Inventory\Packages\Package;
use App\Repositories\Inventory\Packages\PackageRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class PackageService
 * @package App\Services\Inventory\Packages
 */
class PackageService implements PackageServiceInterface
{
    /**
     * @var PackageRepositoryInterface
     */
    private $packageRepository;

    /**
     * PackageService constructor.
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * @param array $params
     * @return Package
     * @throws PackageException
     */
    public function create(array $params): ?Package
    {
        try {
            $this->packageRepository->beginTransaction();

            $package = $this->packageRepository->create($params);

            if (!$package instanceof Package) {
                Log::error('Package hasn\'t been created.', ['params' => $params]);
                $this->packageRepository->rollbackTransaction();

                throw new PackageException('Package hasn\'t been created');
            }

            $this->packageRepository->commitTransaction();

            Log::info('Package has been successfully created', ['id' => $package->id]);
        } catch (\Exception $e) {
            Log::error('Package create error. Params - ' . json_encode($params), $e->getTrace());
            $this->packageRepository->rollbackTransaction();

            throw new PackageException('Package create error');
        }

        return $package;
    }

    /**
     * @param int $id
     * @param array $params
     * @return Package|null
     * @throws PackageException
     */
    public function update(int $id, array $params): ?Package
    {
        try {
            $this->packageRepository->beginTransaction();

            /** @var Package $package */
            $package = $this->packageRepository->update(array_merge($params, ['id' => $id]));

            $this->packageRepository->commitTransaction();

            Log::info('Package has been successfully updated', ['id' => $package->id]);
        } catch (\Exception $e) {
            Log::error('Package update error. Params - ' . json_encode($params), $e->getTrace());
            $this->packageRepository->rollbackTransaction();

            throw new PackageException('Package update error');
        }

        return $package;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $this->packageRepository->beginTransaction();

            $this->packageRepository->delete(['id' => $id]);

            $this->packageRepository->commitTransaction();

            Log::info('Package has been successfully deleted', ['id' => $id]);
        } catch (\Exception $e) {
            Log::error('Package delete error. id - ' . $id, $e->getTrace());
            $this->packageRepository->rollbackTransaction();

            return false;
        }

        return true;
    }
}
