<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory\Images;

use App\Http\Requests\WithDealerRequest;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @property int $dealer_id
 * @property int $inventory_id
 * @property int[] $image_ids
 */
class DeleteImagesRequest extends WithDealerRequest
{
    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;

    public function getRules(): array
    {
        return $this->rules + [
                'inventory_id' => 'integer|min:1|required|exists:inventory,inventory_id',
                'image_ids' => 'array|nullable',
                'image_ids.*' => 'integer|min:1|exists:inventory_image,image_id'
            ];
    }

    /**
     * @return bool
     * @throws ResourceException when there were some validation error
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->all(), $this->getRules());

        if ($validator->fails()) {
            throw new ResourceException('Validation Failed', $validator->errors());
        }

        /** @var Inventory $inventory */
        $inventory = $this->getInventoryRepository()->get(['id' => $this->inventory_id]);

        if ($this->inventory_id && $inventory) {
            $user = $this->getAuthUser();

            if ($user) {
                if ($inventory->dealer_id !== $user->dealer_id) {
                    throw new AccessDeniedHttpException(
                        'You are not allowed to delete images from this inventory'
                    );
                }

                if (empty($this->image_ids)) {
                    return true;
                }

                $allImagesBelongsToCurrentUser = $this->getImageRepository()->getAll([
                    ImageRepositoryInterface::CONDITION_AND_WHERE_IN => ['inventory_image.image_id' => $this->image_ids]
                ])->every(function (\App\Models\Inventory\Image $image) use ($inventory) {
                    return $image->inventory_id === $inventory->inventory_id;
                });

                if (!$allImagesBelongsToCurrentUser) {
                    throw new AccessDeniedHttpException(
                        'You are not allowed to delete those images'
                    );
                }
            }
        }

        return true;
    }

    protected function getAuthUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return Auth::user();
    }

    protected function getInventoryRepository(): InventoryRepositoryInterface
    {
        $this->inventoryRepository = $this->inventoryRepository ?? app(InventoryRepositoryInterface::class);

        return $this->inventoryRepository;
    }

    protected function getImageRepository(): ImageRepositoryInterface
    {
        $this->imageRepository = $this->imageRepository ?? app(ImageRepositoryInterface::class);

        return $this->imageRepository;
    }
}
