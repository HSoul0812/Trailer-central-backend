<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\Expense;

/**
 * @author Marcel
 */
class ExpenseRepository implements ExpenseRepositoryInterface {

    public function create($params) {
        $categories = $params['categories'];
        unset($params['categories']);
        $expense = Expense::create($params);
        if (is_array($categories)) {
            $expense->categories()->createMany($categories);
        }
        return $expense->refresh();
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

}
