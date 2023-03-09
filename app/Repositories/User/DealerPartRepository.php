<?php

namespace App\Repositories\User;

use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\DealerPart;
use Illuminate\Support\Facades\DB;

class DealerPartRepository  implements DealerPartRepositoryInterface
{
    use Transaction;

    /**
     * @param array $params
     * @return DealerPart
     */
    public function create($params): DealerPart
    {
        $dealerPart = new DealerPart();

        $dealerPart->fill($params)->save();

        return $dealerPart;
    }

    public function get($params)
    {
        $dealerPart = DealerPart::where(['dealer_id' => $params['dealer_id']])->first();

        return $dealerPart;
    }

    public function update($params): DealerPart
    {
        $dealerPart = DealerPart::where(['dealer_id' => $params['dealer_id']])->first();

        $dealerPart->fill($params)->save();

        return $dealerPart;
    }

    /**
     * @param $params
     * @return bool
     */
    public function delete($params): bool
    {
        $dealerPart = DealerPart::where(['dealer_id' => $params['dealer_id']])->first();

        return $dealerPart->delete();
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }

}
