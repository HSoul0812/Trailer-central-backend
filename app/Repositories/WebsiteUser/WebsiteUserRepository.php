<?php

namespace App\Repositories\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Database\Eloquent\Collection;

class WebsiteUserRepository implements WebsiteUserRepositoryInterface
{
    public function get($attributes): Collection
    {
        return WebsiteUser::where($attributes)->get();
    }

    public function findOrFail($userId): WebsiteUser
    {
        return WebsiteUser::findOrFail($userId);
    }

    public function create(array $attributes): WebsiteUser
    {
        $websiteUser = new WebsiteUser();
        $websiteUser->fill($attributes);
        $websiteUser->password = $attributes['password'] ?? '';
        $websiteUser->tc_user_id = $attributes['tc_user_id'];
        $websiteUser->save();

        return $websiteUser;
    }

    public function update($id, array $newAttributes): bool
    {
        $websiteUser = WebsiteUser::find($id);
        $websiteUser->fill($newAttributes);
        if (isset($newAttributes['tc_user_location_id'])) {
            $websiteUser->tc_user_location_id = $newAttributes['tc_user_location_id'];
        }

        return $websiteUser->save();
    }

    public function delete($id): bool
    {
        return WebsiteUser::find($id)->delete();
    }
}
