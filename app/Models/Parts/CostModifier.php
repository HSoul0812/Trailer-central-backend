<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class CostModifier extends Model { 
    
    const NO_MODIFIER = 0;
    const MODIFY_BY_TEN_PERCENT = 10;
    const MODIFY_BY_TWENTY_PERCENT = 20;
    const MODIFY_BY_THIRTY_PERCENT = 30;
    const MODIFY_BY_FORTY_PERCENT = 40;
    const MODIFY_BY_FIFTY_PERCENT = 50;
    const MODIFY_BY_SIXTY_PERCENT = 60;
    const MODIFY_BY_SEVENTY_PERCENT = 70;
    const MODIFY_BY_EIGHTY_PERCENT = 80;
    const MODIFY_BY_NINETY_PERCENT = 90;
    const MODIFY_BY_HUNDRED_PERCENT = 100;
    
    protected $table = 'parts_price_modifier';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'dealer_id',
        'modifier'
    ];
    
     /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'modifier' => 'integer',
    ];
    
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
    
    public function dealer()
    {
        return $this->hasOne(User::class);
    }
}
