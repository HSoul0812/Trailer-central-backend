<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealerLogo extends Model
{
    use TableAware;

    protected $fillable = [
        'dealer_id',
        'filename',
        'benefit_statement'
    ];

    public function dealer(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'dealer_id', 'user_id');
    }
}
