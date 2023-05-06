<?php

namespace App\Transformers\User;

use App\Repositories\User\UserRepositoryInterface;
use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class OverlaySettingsTransformer extends TransformerAbstract
{
    public function transform(User $user): array
    {
        $payload = [
            'overlay_enabled' => $user->overlay_enabled,
            'overlay_default' => $user->overlay_default,
            'overlay_logo' => $user->overlay_logo,
            'overlay_logo_position' => $user->overlay_logo_position,
            'overlay_logo_width' => $user->overlay_logo_width,
            'overlay_logo_height' => $user->overlay_logo_height,
            'overlay_upper' => $user->overlay_upper,
            'overlay_upper_bg' => $user->overlay_upper_bg,
            'overlay_upper_alpha' => $user->overlay_upper_alpha,
            'overlay_upper_text' => $user->overlay_upper_text,
            'overlay_upper_size' => $user->overlay_upper_size,
            'overlay_upper_margin' => $user->overlay_upper_margin,
            'overlay_lower' => $user->overlay_lower,
            'overlay_lower_bg' => $user->overlay_lower_bg,
            'overlay_lower_alpha' => $user->overlay_lower_alpha,
            'overlay_lower_text' => $user->overlay_lower_text,
            'overlay_lower_size' => $user->overlay_lower_size,
            'overlay_lower_margin' => $user->overlay_lower_margin,
            'overlay_has_batch' => false
        ];

        if ($user->overlay_enabled !== null) {
            $payload['overlay_has_batch'] = $this->getRepository()->hasRunningOverlayBatch($user->dealer_id);
        }

        return $payload;
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return app(UserRepositoryInterface::class);
    }
}
