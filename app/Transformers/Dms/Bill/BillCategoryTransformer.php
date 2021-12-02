<?php
namespace App\Transformers\Dms\Bill;

use League\Fractal\TransformerAbstract;

class BillCategoryTransformer extends TransformerAbstract
{
    public function transform($category)
    {
        return [
            'account_name' => $category->account ? $category->account->name : '',
            'account_id' => $category->account_id,
            'description' => $category->description,
            'amount' => $category->amount,
            'bill_id' => $category->bill_id,
            'id' => $category->id
        ];
    }
}