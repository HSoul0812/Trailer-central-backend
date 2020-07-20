<?php


namespace App\Models\CRM\Quickbooks;


use Illuminate\Database\Eloquent\Model;

class ItemNew extends Model
{
    protected $table = 'qb_items_new';

    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'name',
        'description',
        'type',
        'sub_item',
        'parent_id',
        'is_default',
        'in_simple_mode',
    ];
}
