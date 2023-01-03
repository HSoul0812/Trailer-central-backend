<?php


namespace App\Repositories\Pos;


use App\Models\Pos\Quote;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

class QuoteRepository extends RepositoryAbstract
{
    public function __construct(Quote $model) {
        $this->model = $model;
    }

    public function create($params) {
        DB::beginTransaction();

        try {
            $quote = $this->model->create($params);

             DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();

            throw new \Exception($ex->getMessage());
        }


       return $quote;
    }
}
