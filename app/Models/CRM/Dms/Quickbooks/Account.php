<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Account extends Model
{
    protected $table = 'qb_accounts';

    protected $guarded = ['qb_id'];

    public function parent()
    {
        return $this->hasOne(Account::class, 'id', 'parent_id');
    }
}
