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
        $websiteUser->password = $attributes['password'];
        $websiteUser->save();
        return $websiteUser;
    }

    public function update($id, array $newAttributes): bool
    {
        // TODO: Implement update() method.
        $websiteUser = WebsiteUser::find($id);
        return $websiteUser->update($newAttributes);
    }

    public function delete($id): bool
    {
        return WebsiteUser::find($id)->delete();
    }
}
