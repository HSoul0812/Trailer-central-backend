<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class DealerPasswordReset extends Model {
    
    const STATUS_PASSWORD_RESET_INITIATED = 1;
    const STATUS_PASSWORD_RESET_COMPLETED = 2;
    
    const TABLE_NAME = 'dealer_password_reset';
    
    public $timestamps = false;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    
    protected $fillable = [
        'id',
        'code',
        'dealer_id',
        'date_created',
        'status'
    ];
    
    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }
    
}
