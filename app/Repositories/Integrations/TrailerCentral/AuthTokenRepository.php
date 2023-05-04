<?php

namespace App\Repositories\Integrations\TrailerCentral;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use stdClass;

class AuthTokenRepository implements AuthTokenRepositoryInterface
{
    public function get(array $params): Model|stdClass|null
    {
        $query = DB::connection('mysql')
            ->table('auth_token');
        if (isset($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        return $query->first();
    }
}
